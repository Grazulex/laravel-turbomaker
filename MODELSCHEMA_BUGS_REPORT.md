# ModelSchema Enterprise - Bug Report & Feature Requests

**Project**: Laravel ModelSchema Enterprise  
**Reporter**: TurboMaker Migration Team  
**Date**: 4 août 2025  
**Context**: Integration during TurboMaker Phase 7 migration  

## 🐛 Critical Bugs

### 1. Seeder Generator Missing/Non-Functional
**Priority**: HIGH  
**Severity**: Breaking  

**Description**: 
When requesting seeder generation via options, ModelSchema Enterprise does not generate seeder files.

**Steps to Reproduce**:
1. Create ModelSchema with seeder option enabled
2. Call `generationService->generateAll()` with `'seeder' => true`
3. Check if seeder fragment/file is returned

**Expected**: Seeder fragment/file should be generated  
**Actual**: No seeder in results array  

**Impact**: Users cannot generate seeders, breaking workflow for data seeding

**Test Case**:
```php
$options = ['seeder' => true, 'model' => true];
$results = $generationService->generateAll($modelSchema, $options);
// $results should contain 'seeder' key but doesn't
```

---

### 2. Performance Inconsistency on Small Datasets
**Priority**: MEDIUM  
**Severity**: Performance  

**Description**: 
Fragment Architecture shows inconsistent performance benefits on small test datasets.

**Expected**: Fragment mode should always be faster than file-writing mode  
**Actual**: Sometimes file mode performs better on small datasets (< 5 files)

**Performance Data**:
- Fragment Mode: 0.008701s
- File Mode: 0.008482s  
- Expected: Fragment < File

**Impact**: Performance promises not consistently met

---

## ⚠️ Potential Issues (Need Verification)

### 3. Observer Generator Completeness
**Priority**: MEDIUM  

**Need to Test**: Does ModelSchema Enterprise generate complete Observer classes with all standard event methods?

**Expected Methods**:
- creating, created, updating, updated, saving, saved, deleting, deleted, restoring, restored, retrieved

### 4. Policy Generator Authorization Methods
**Priority**: MEDIUM  

**Need to Test**: Does ModelSchema Enterprise generate Policy classes with proper authorization methods?

**Expected Methods**:
- viewAny, view, create, update, delete, restore, forceDelete

---

## 🚀 Feature Requests

### 1. Debug/Verbose Mode
**Priority**: LOW  

**Description**: Add debug mode to see what generators are being called and what they return.

**Use Case**: Integration debugging when generators don't produce expected output

### 2. Generator Registry Introspection
**Priority**: LOW  

**Description**: Method to list available generators and their capabilities

**Use Case**: Dynamic feature detection for adapter layers

### 3. Enhanced Error Reporting
**Priority**: MEDIUM  

**Description**: Better error messages when generation fails, including which specific generator failed

---

## 🔧 Integration Context

**TurboMaker Architecture**:
```
TurboMaker CLI Commands
         ↓
ModelSchemaGenerationAdapter (Bridge)
         ↓
ModelSchema Enterprise GenerationService
         ↓
Fragment Architecture
```

**Current Workaround for Seeder Bug**:
We implemented a fallback in `ModelSchemaGenerationAdapter.addMissingGenerators()` that manually generates seeders when ModelSchema doesn't provide them.

**Testing Environment**:
- Laravel 11.x
- PHP 8.2+
- TurboMaker test suite
- ModelSchema Enterprise latest version

---

## 📞 Contact

For questions about these issues, contact the TurboMaker migration team or reference the integration code in `ModelSchemaGenerationAdapter.php`.

**Status**: Most issues have workarounds implemented, but fixes in ModelSchema Enterprise would improve the integration quality.
