# HealthTrack Domain Architecture

## 1. Overview

This document describes the domain architecture for a regulated health-tech system supporting **multiple chronic conditions per user**, **medication management**, **glucose–medication correlation**, and a **modular rule engine** for treatment suggestions. The design follows Domain-Driven Design (DDD), event-driven communication, and clear bounded contexts so future features (time-in-range, HbA1c, drug interactions, adherence scoring, predictive risk, physician dashboards) can be layered without refactoring core modules.

---

## 2. Bounded Contexts

| Context | Responsibility | Key Aggregates | Events Published |
|--------|----------------|----------------|------------------|
| **Glucose** | Blood glucose time-series: ingestion, normalization, classification, storage | `GlucoseReading` | `GlucoseReadingRecorded`, `GlucoseReadingCritical` |
| **Condition** | User chronic conditions, condition-based treatment plans, physician overrides (future) | `UserCondition`, `TreatmentPlan` | `ConditionAssigned`, `TreatmentPlanUpdated` |
| **Medication** | Drug master data, user prescriptions, administration logs | `Drug`, `Prescription`, `AdministrationLog` | `PrescriptionCreated`, `MedicationAdministered` |
| **Adherence** | Medication adherence scoring, missed doses, compliance metrics | `AdherenceSummary` (read model) | (consumes Medication/Glucose events) |
| **Analytics** | Aggregations, trends, time-in-range, HbA1c estimation, risk scoring | Read models / projections | (consumes all domain events) |
| **Rules** | Rule definitions (versioned), evaluation engine, medication adjustment suggestions | `RuleDefinition`, `RuleEvaluationResult` | `MedicationSuggestionGenerated`, `UncontrolledConditionFlagged` |

All contexts are **strictly user-scoped** (authenticated user id on every query and event). Records are **audit-friendly** (timestamps, optional actor) and **immutable where appropriate** (e.g. administration logs, event payloads).

---

## 3. Core Data Separation (Medication Context)

### 3.1 Medication Master Data (Drug Definitions)

- **Purpose**: Reference data; shared across users; changes are versioned.
- **Examples**: Drug name, form (tablet, injection), unit, therapeutic class, insulin type (basal, bolus, sliding-scale), interaction groups.
- **Immutability**: Updates create new versions or new records; historical references remain valid.
- **Scope**: Global (admin/maintained), not per-user.

### 3.2 User Prescriptions

- **Purpose**: What is prescribed to a **specific user** for a **specific condition**: dosage, schedule, start/end dates, prescribing physician (future), link to condition.
- **Examples**: “Metformin 500mg BID”, “Basal insulin 10 units at 22:00”, “Sliding scale insulin (rule set X)”.
- **Immutability**: Prescription changes (dose, end date) are new versions or new rows; history preserved for audit.
- **Scope**: Per user, per condition.

### 3.3 Administration Logs

- **Purpose**: What was **actually taken** and **when**: timestamp, dose, optional relation to glucose reading or meal.
- **Immutability**: Append-only; no updates/deletes (only corrections as new entries if required by policy).
- **Scope**: Per user; linked to prescription (and optionally to condition/glucose).

This separation keeps **master data**, **prescription intent**, and **actual behavior** clearly separated and audit-friendly.

---

## 4. Condition and Treatment Plans

- **User conditions**: Each user can have multiple chronic conditions (e.g. Diabetes Type 1, Diabetes Type 2, Hypertension, Hyperlipidemia). Stored as `UserCondition` (user_id, condition_type, diagnosed_at, status, etc.).
- **Treatment plans**: Condition-based plans that reference prescriptions and (future) physician overrides. Plans can specify which rules apply (e.g. sliding scale for Type 1, A1c targets for Type 2).
- **Future**: Physician overrides (dose changes, plan changes) as first-class events and versioned records.

---

## 5. Rule Engine Design

### 5.1 Principles

- **Modular**: Rules are discrete units (e.g. “sliding scale insulin”, “7-day average hyperglycemia flag”).
- **Versionable**: Each rule has a version; evaluations record which rule version ran. Historical data stays consistent with the version that was active at evaluation time.
- **Decoupled**: Engine has no dependency on controllers or persistence; it receives **inputs** (glucose readings, prescriptions, administration logs, aggregates) and returns **outputs** (suggestions, flags). Persistence and API layers call the engine and store results.
- **Evolvable**: New or updated rule definitions do not break past evaluations; old results remain tied to old rule versions.

### 5.2 Inputs (Context for Evaluation)

- Latest glucose reading(s) and optional short history.
- Aggregates (e.g. 7-day average, time-in-range) when available.
- Active prescriptions for the user (and condition) relevant to the rule.
- Recent administration logs (to avoid duplicate suggestions or to suggest “next dose”).

### 5.3 Outputs

- **Medication suggestions**: e.g. “Sliding scale: 4 units based on current glucose 180 mg/dL” (rule_id, version, inputs, result).
- **Flags**: e.g. “Uncontrolled diabetes (7-day avg > 180)” (rule_id, version, summary).
- All outputs reference **rule_id + version** and are stored for audit and display.

### 5.4 Versioning and History

- Rule definitions stored with `id`, `version`, `effective_from`, `payload` (e.g. JSON or structured config). Evaluations store `rule_id`, `rule_version`, `evaluated_at`, `input_snapshot`, `output`. Historical data remains valid when rules change.

---

## 6. Event-Driven Workflow: New Glucose Reading

End-to-end flow when a new glucose reading is created:

```
1. API Request (POST /glucose)
       ↓
2. Validation (FormRequest)
       ↓
3. Glucose Context: normalize → validate range → classify → persist
       ↓
4. Event: GlucoseReadingRecorded(reading)  [new event]
       ↓
5. Event: GlucoseReadingCritical(reading)  [if requires_alert]
       ↓
6. Listeners (async, queue):
   - RuleEngineListener: load user context (conditions, prescriptions, recent logs, aggregates)
                        → run applicable rules (e.g. sliding scale, 7-day check)
                        → persist RuleEvaluationResult
                        → if suggestion: MedicationSuggestionGenerated(suggestion)
                        → if flag: UncontrolledConditionFlagged(flag)
   - AnalyticsListener: update read models (trends, averages, TIR, HbA1c projection)
   - AdherenceListener: (if correlated with medication timing) update adherence view
   - AuditListener: append to audit trail (who, what, when, payload hash)
       ↓
7. Alert/Notification (consumes GlucoseReadingCritical / MedicationSuggestionGenerated)
       ↓
8. Response to client (201 + reading)
```

- **Normalization & classification**: Remain in Glucose context (existing `GlucoseService`).
- **Rule evaluation**: Triggered by `GlucoseReadingRecorded` (and optionally `MedicationAdministered`); runs in a dedicated listener that calls the **Rule Engine** with user-scoped context.
- **Suggestions and flags**: Emitted as events; persistence and alerts are listeners, not inside controllers or models.
- **Audit trail**: Every material step can append an immutable audit record (user_id, action, entity, timestamp, payload or hash).

---

## 7. Event Catalog (Summary)

| Event | Context | When | Main Payload |
|-------|---------|------|--------------|
| `GlucoseReadingRecorded` | Glucose | After reading persisted | reading id, user_id, value_mg_dl, classification, measured_at |
| `GlucoseReadingCritical` | Glucose | When requires_alert | reading |
| `MedicationSuggestionGenerated` | Rules | After rule suggests dose/action | rule_id, version, suggestion payload, user_id |
| `UncontrolledConditionFlagged` | Rules | When rule flags control | rule_id, condition, summary |
| `PrescriptionCreated` / `Updated` | Medication | When prescription changes | prescription, user_id |
| `MedicationAdministered` | Medication | When log entry created | log id, user_id, prescription_id, taken_at, dose |
| `ConditionAssigned` | Condition | When user condition added | user_id, condition |
| `TreatmentPlanUpdated` | Condition | When plan changes | user_id, plan |

All events carry `user_id` (and optionally `occurred_at`, `correlation_id`) for scoping and audit.

---

## 8. Layering Future Features (No Core Refactor)

- **Time-in-range / HbA1c**: Implemented in **Analytics** context; consumes `GlucoseReadingRecorded` (and existing readings), builds projections; rule engine can consume these projections as **inputs**.
- **Drug interaction checks**: **Medication** context: use master data + current prescriptions; run on prescription change or before suggestion; emit events or block with reason.
- **Adherence scoring**: **Adherence** context: consumes `MedicationAdministered` and prescription schedules; writes to read model; no change to Glucose or Medication core writes.
- **Predictive hypoglycemia risk**: **Analytics** context: model trained/run on glucose (and optionally administration) data; output as risk score; rules or alerts can consume it.
- **Physician dashboards**: Read from **Analytics** and **Adherence** read models and from event-sourced or audited history; no change to core write paths.

Core modules (Glucose, Medication, Condition) stay focused on their aggregates and events; new capabilities are new listeners, new read models, and new rule definitions.

---

## 9. Security, Compliance, and Scalability

- **User scoping**: Every query and event filtered by authenticated user id; no cross-user access in application logic.
- **Audit**: Immutable audit log for material actions; retention and export per policy.
- **Immutability**: Administration logs and event payloads immutable; prescriptions and rule results versioned so history is clear.
- **Regulated environment**: Design supports traceability (rule version, input snapshot, output), clear separation of master data vs user data, and event-driven audit trail.
- **Scalability**: Event-driven and async listeners allow scaling workers; read models (Analytics, Adherence) can be built on separate stores/caches; rule engine is stateless and can be scaled independently.

---

## 10. Directory and Module Skeleton

Suggested layout (aligned with Laravel and DDD):

```
app/
  Domains/
    Glucose/           # existing + GlucoseReadingRecorded event
    Condition/         # UserCondition, TreatmentPlan, events
    Medication/        # Drug, Prescription, AdministrationLog, events
    Adherence/         # read models, listeners
    Analytics/         # read models, TIR, HbA1c, listeners
  RuleEngine/          # rule definitions (versioned), evaluator, inputs/outputs
  Events/              # or keep under Domains/* (Glucose, Medication, etc.)
  Listeners/           # RuleEngineListener, AnalyticsListener, AuditListener, etc.
```

Persistence: each context can own its tables (e.g. `drugs`, `prescriptions`, `administration_logs`, `user_conditions`, `treatment_plans`, `rule_definitions`, `rule_evaluation_results`, `medication_suggestions`). Analytics/Adherence use read-model tables or caches populated by listeners.

This document is the single source of truth for the domain architecture; implementation should follow these boundaries and event contracts.

---

## 11. Implementation Summary

| Layer | Location | Notes |
|-------|----------|--------|
| **Architecture doc** | `docs/architecture/domain-architecture.md` | This file |
| **Glucose events** | `app/Events/Glucose/GlucoseReadingRecorded.php`, `GlucoseReadingCritical.php` | Dispatched after persist; trigger rules + alerts |
| **Rule events** | `app/Events/Rules/MedicationSuggestionGenerated.php`, `UncontrolledConditionFlagged.php` | Emitted by rule listener |
| **Rule engine** | `app/RuleEngine/` | `RuleEngineInterface`, `RuleContext`, `RuleDefinitionInterface`, `RuleEngine`, `GlucoseRuleContext`, `SlidingScaleInsulinRule` |
| **Rule listener** | `app/Listeners/Glucose/EvaluateGlucoseRulesListener.php` | Listens to `GlucoseReadingRecorded`, builds context, runs rules, dispatches suggestion/flag events |
| **Condition** | `app/Models/UserCondition.php`, migration `user_conditions` | Multiple conditions per user |
| **Medication** | `app/Models/Drug.php`, `Prescription.php`, `AdministrationLog.php`; migrations `drugs`, `prescriptions`, `administration_logs` | Master data, prescriptions, append-only logs |
| **Rule persistence** | Migrations `rule_definitions`, `rule_evaluation_results` | Versioned rules and audit of evaluations |

**Flow (current):** `POST /glucose` → `GlucoseService::createReading()` → `GlucoseReadingRecorded` → `EvaluateGlucoseRulesListener` → rule engine → `MedicationSuggestionGenerated` / `UncontrolledConditionFlagged` (when applicable).
