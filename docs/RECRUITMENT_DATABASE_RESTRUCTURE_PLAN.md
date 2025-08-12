# Recruitment Database Restructure Plan

## Overview

This document outlines the plan to restructure the recruitment database from a single polymorphic `recruitment_assessments` table to separate tables for each recruitment stage.

## Completed Work ✅

### 1. Database Structure Changes

-   ✅ Created 8 new stage-specific tables:
    -   `recruitment_cv_reviews`
    -   `recruitment_psikotes`
    -   `recruitment_tes_teori`
    -   `recruitment_interviews`
    -   `recruitment_offerings`
    -   `recruitment_mcu`
    -   `recruitment_hiring`
    -   `recruitment_onboarding`
-   ✅ All tables use `int auto increment` for primary keys
-   ✅ All tables use `uuid` for `session_id` foreign keys
-   ✅ Dropped old `recruitment_assessments` and `recruitment_offers` tables
-   ✅ Recreated `recruitment_documents` table with new structure

### 2. Model Updates

-   ✅ Created 8 new Eloquent models for each stage
-   ✅ Updated `RecruitmentSession` model with new relationships
-   ✅ Added helper methods: `getAssessmentByStage()`, `isStageCompleted()`, `getAllAssessments()`, `getCompletedAssessmentsCount()`
-   ✅ Removed old `RecruitmentAssessment` and `RecruitmentOffer` models
-   ✅ Fixed table name issues for models with non-standard pluralization

### 3. Service Layer Updates

-   ✅ Updated `RecruitmentSessionService` to use new models
-   ✅ Implemented auto-advancement logic for each stage
-   ✅ Updated assessment processing methods for all stages
-   ✅ Updated `getSessionTimeline()` method to use new relationships
-   ✅ Fixed assessment validation logic to use new helper methods

### 4. Controller Updates

-   ✅ Updated `RecruitmentSessionController` to use new relationships
-   ✅ Updated `RecruitmentRequestController` to use new relationships
-   ✅ Updated `RecruitmentCandidateController` to use new relationships
-   ✅ Removed import of old `RecruitmentAssessment` model
-   ✅ Updated `getSessionData()` method to use new structure
-   ✅ Updated eager loading in all methods
-   ✅ Fixed `destroy()` method to delete new relationships
-   ✅ Updated `getExpectedCurrentStage()` method to use helper methods

### 5. View Updates

-   ✅ Updated `show-session.blade.php` to use new assessment structure
-   ✅ Updated assessments table to display data from new models
-   ✅ Added proper status mapping for all assessment types
-   ✅ Added score/result display logic for each stage

### 6. Testing

-   ✅ Created sample data for testing
-   ✅ Verified model relationships work correctly
-   ✅ Verified service methods work correctly
-   ✅ Verified controller methods work correctly
-   ✅ Fixed DataTables error related to old relationships

## Current Status

### ✅ Completed

-   Database restructuring
-   Model layer updates
-   Service layer updates
-   Controller layer updates
-   View layer updates
-   Basic testing
-   Error fixes for DataTables

### 🔄 In Progress

-   Integration with letter number system
-   Integration with administration table for hiring

### ❌ Pending

-   Comprehensive testing (unit, integration, e2e)
-   Performance optimization
-   Final cleanup of old code

## Technical Details

### New Table Structure

All new tables follow this pattern:

```sql
CREATE TABLE recruitment_[stage_name] (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id CHAR(36) NOT NULL,
    [stage_specific_columns] NULL,
    notes TEXT NULL,
    reviewed_by BIGINT UNSIGNED NOT NULL,
    reviewed_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (session_id) REFERENCES recruitment_sessions(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);
```

### Auto-Advancement Logic

-   If assessment passes → automatically advance to next stage
-   If assessment fails → immediately reject session
-   Sequential validation prevents skipping stages

### Assessment Status Mapping

-   CV Review: `recommended` / `not_recommended`
-   Psikotes: `pass` / `fail`
-   Tes Teori: `pass` / `fail`
-   Interviews: `recommended` / `not_recommended`
-   Offering: `accepted` / `rejected` / `negotiating`
-   MCU: `fit` / `unfit` / `follow_up`
-   Hiring: Record exists = completed
-   Onboarding: Record exists = completed

### Fixed Issues

-   ✅ DataTables error: "Call to undefined relationship [assessments]"
-   ✅ Updated all controller methods to use new relationships
-   ✅ Fixed eager loading in all queries
-   ✅ Updated assessment validation logic
-   ✅ Fixed table name issues for non-standard pluralization

## Next Steps

### 1. Integration Tasks

-   [ ] Integrate with letter number system for offering and hiring
-   [ ] Integrate with administration table for hiring data
-   [ ] Implement letter number generation logic

### 2. Testing Tasks

-   [ ] Unit tests for all new models
-   [ ] Integration tests for service methods
-   [ ] End-to-end tests for recruitment workflow
-   [ ] Performance testing with large datasets

### 3. Optimization Tasks

-   [ ] Review and optimize database indexes
-   [ ] Implement caching for frequently accessed data
-   [ ] Optimize queries with proper eager loading

### 4. Cleanup Tasks

-   [ ] Remove unused old service methods
-   [ ] Clean up old migration files
-   [ ] Update API documentation
-   [ ] Update user manual

## Migration History

### Migration Files Created

1. `2025_08_07_150000_create_recruitment_cv_reviews_table.php`
2. `2025_08_07_150001_create_recruitment_psikotes_table.php`
3. `2025_08_07_150002_create_recruitment_tes_teori_table.php`
4. `2025_08_07_150003_create_recruitment_interviews_table.php`
5. `2025_08_07_150004_create_recruitment_offerings_table.php`
6. `2025_08_07_150005_create_recruitment_mcu_table.php`
7. `2025_08_07_150008_create_recruitment_hiring_table.php`
8. `2025_08_07_150011_create_recruitment_onboarding_table.php`
9. `2025_08_07_150004_remove_recruitment_documents_foreign_key_constraint.php`
10. `2025_08_07_150009_drop_recruitment_documents_table.php`
11. `2025_08_07_150010_create_recruitment_documents_table_new.php`
12. `2025_08_07_150012_drop_old_recruitment_tables.php`
13. `2025_08_07_150013_migrate_recruitment_assessment_data.php`

### Model Files Created

1. `app/Models/RecruitmentCvReview.php`
2. `app/Models/RecruitmentPsikotes.php`
3. `app/Models/RecruitmentTesTeori.php`
4. `app/Models/RecruitmentInterview.php`
5. `app/Models/RecruitmentOffering.php`
6. `app/Models/RecruitmentMcu.php`
7. `app/Models/RecruitmentHiring.php`
8. `app/Models/RecruitmentOnboarding.php`

## Notes

-   Data migration was attempted but old tables were dropped before migration could complete
-   Sample data was created manually for testing purposes
-   All new models use proper table name specifications to handle non-standard pluralization
-   Auto-advancement logic ensures proper workflow progression
-   Sequential validation prevents stage skipping
-   All DataTables errors have been resolved
-   All controller methods now use the new relationship structure
-   Route updated: `/sessions/session/{id}` → `/sessions/candidate/{id}` (route name: `show-session` → `candidate`)
