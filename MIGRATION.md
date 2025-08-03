# Migration vers Laravel ModelSchema

## 📋 Vue d'ensemble

Ce docume### ✅ **Phases Terminées**

#### Phase 1: Installation et Configuration ✅ COMPLETED
- Package laravel-modelschema installé et fonctionnel
- Tests d'intégration validés  
- Service résolu correctement via DI

#### Phase 2: Création des Adaptateurs ✅ COMPLETED  
- `ModelSchemaAdapter` : Conversion bidirectionnelle TurboMaker ↔ ModelSchema
- `FragmentAdapter` : Génération de fragments (fillable, casts, validation, relationships)
- `FieldTypeAdapter` : Migration des field types vers système de plugins
- 11 tests de migration passent avec 60 assertions
- Organisation des tests avec groupes Pest 3

#### Phase 3: Migration du Schema Parser ✅ COMPLETED
- `SchemaParserAdapter` créé avec pattern de composition
- `TurboSchemaManager` utilise maintenant `SchemaParserAdapter`
- Compatibilité totale préservée (tous les 128 tests passent)
- Tests organisés avec groupes Pest 3 (`migration`, `adapters`, `schema-parser`)
- 15 tests de migration passent avec 76 assertions

### 🚧 **Phase en Cours : Phase 4**plan complet de migration de TurboMaker vers le package externe `laravel-modelschema` pour centraliser la gestion des schémas YAML.

### 🎯 Objectifs
- Centraliser la gestion YAML entre TurboMaker et Arc
- Bénéficier des fonctionnalités avancées (plugins, optimisation, diff)
- Simplifier la maintenance et améliorer la performance
- Conserver la compatibilité avec l'API existante

### 🧪 Stratégie de Tests

Cette migration utilise **Pest 3 Groups** pour organiser et distinguer les tests :

#### Groupes de Tests Définis
- **`migration`** : Tous les tests liés à la migration vers laravel-modelschema
- **`integration`** : Tests d'intégration spécifiques au package ModelSchema
- **`adapters`** : Tests pour les adaptateurs de conversion (ModelSchemaAdapter, FragmentAdapter, SchemaParserAdapter)
- **`fragments`** : Tests spécifiques à la gestion des fragments
- **`schema-parser`** : Tests spécifiques à l'adaptateur SchemaParser
- **`legacy`** : Tests de compatibilité avec l'ancien système

#### Commandes de Test Utiles
```bash
# Exécuter seulement les tests de migration
./vendor/bin/pest --group=migration

# Exécuter les tests d'adaptateurs
./vendor/bin/pest --group=adapters

# Exécuter les tests de l'adaptateur SchemaParser spécifiquement  
./vendor/bin/pest --group=schema-parser

# Exécuter tous les tests sauf ceux de migration (tests existants)
./vendor/bin/pest --exclude-group=migration

# Exécuter les tests de compatibilité
./vendor/bin/pest --group=legacy
```

#### Organisation des Nouveaux Tests
- `tests/Unit/Adapters/` : Tests des adaptateurs avec groupe `adapters`
- `tests/Unit/ModelSchema*` : Tests d'intégration avec groupe `modelschema`
- `tests/Feature/Migration*` : Tests fonctionnels avec groupe `migration`

---

## 🗂 Composants à Migrer

### Fichiers Principaux à Remplacer/Adapter
- [ ] `src/Schema/SchemaParser.php` → `SchemaService`
- [ ] `src/TurboSchemaManager.php` → `SchemaService` + `GenerationService`
- [ ] `src/Schema/Schema.php` → Adapter vers fragments
- [ ] `src/Schema/Field.php` → Utiliser field types du package externe
- [ ] `src/Schema/Relationship.php` → Utiliser relations du package externe

### Commandes à Adapter
- [ ] `src/Console/Commands/TurboSchemaCommand.php`
- [ ] `src/Console/Commands/TurboMakeCommand.php`

### Générateurs à Modifier
- [ ] `src/Generators/BaseGenerator.php`
- [ ] `src/Generators/ModelGenerator.php`
- [ ] `src/Generators/MigrationGenerator.php`
- [ ] `src/Generators/RequestGenerator.php`
- [ ] `src/Generators/ResourceGenerator.php`
- [ ] `src/Generators/FactoryGenerator.php`
- [ ] Tous les autres générateurs

## 📊 Status Global de la Migration

### ✅ **Phases Terminées**

#### Phase 1: Installation et Configuration ✅ COMPLETED
- Package laravel-modelschema installé et fonctionnel
- Tests d'intégration validés  
- Service résolu correctement via DI

#### Phase 2: Création des Adaptateurs ✅ COMPLETED  
- `ModelSchemaAdapter` : Conversion bidirectionnelle TurboMaker ↔ ModelSchema
- `FragmentAdapter` : Génération de fragments (fillable, casts, validation, relationships)
- `FieldTypeAdapter` : Migration des field types vers système de plugins
- 11 tests de migration passent avec 60 assertions
- Organisation des tests avec groupes Pest 3

### � **Phase en Cours : Phase 3**

#### Prochaines Étapes (Phase 4: Migration du TurboSchemaManager)
- [ ] **4.1** Remplacer `resolveSchema()` par l'approche fragments
- [ ] **4.2** Migrer `validateSchema()` vers le package externe
- [ ] **4.3** Adapter `parseFieldsShorthand()` pour utiliser les plugins
- [ ] **4.4** Migrer la création de fichiers schema
- [ ] **4.5** Adapter `listSchemas()` et `schemaExists()`

### 📈 **Métriques de Progression**
- **Tests Migration** : 15 tests ✅ (76 assertions)
- **Tests Existants** : 128 tests ✅ (628 assertions) 
- **Compatibilité** : 100% des tests existants passent
- **Coverage Migration** : SchemaParser complètement migré via adaptateur

---

### Phase 1: Installation et Configuration ✅ COMPLETED
#### Tâches
- [x] **1.1** Installer le package `laravel-modelschema`
  ```bash
  composer require grazulex/laravel-modelschema
  ```
- [x] **1.2** Configurer le service provider
- [x] **1.3** Publier les configurations si nécessaire
- [x] **1.4** Tester l'installation de base

**Status:** ✅ Phase complètement terminée avec succès
- Package installé et fonctionnel
- Tests d'intégration créés et passent
- Service résolu correctement via DI

### Phase 2: Création des Adaptateurs ✅ COMPLETED
#### Tâches
- [x] **2.1** Créer `src/Adapters/ModelSchemaAdapter.php`
  - Wrapper pour `SchemaService`
  - Conversion Schema TurboMaker ↔ ModelSchema
  - Interface compatible avec l'existant
  
- [x] **2.2** Créer `src/Adapters/FragmentAdapter.php`
  - Conversion fragments → format TurboMaker
  - Gestion des templates existants
  - Mapping des données

- [x] **2.3** Créer `src/Adapters/FieldTypeAdapter.php`
  - Migration des field types TurboMaker vers plugins
  - Gestion de la compatibilité

**Status:** ✅ Phase complètement terminée avec succès
- Tous les adaptateurs créés et testés
- Tests organisés avec groupes Pest 3 (`migration`, `adapters`, `fragments`)
- 11 tests passent avec 60 assertions

#### 🧪 Organisation des Tests avec Groupes Pest
Pour cette migration, nous utilisons les **groupes Pest 3** pour organiser les tests :

```bash
# Exécuter tous les tests liés à la migration
./vendor/bin/pest --group=migration

# Exécuter uniquement les tests des adaptateurs
./vendor/bin/pest --group=adapters

# Exécuter uniquement les tests de fragments
./vendor/bin/pest --group=fragments

# Exécuter uniquement les tests d'intégration
./vendor/bin/pest --group=integration
```

**Structure des groupes :**
- `migration` : Tous les tests liés à cette migration vers laravel-modelschema
- `adapters` : Tests spécifiques aux adaptateurs (ModelSchemaAdapter, FragmentAdapter, FieldTypeAdapter)
- `fragments` : Tests de génération de fragments
- `integration` : Tests d'intégration avec le package laravel-modelschema

**Syntaxe utilisée :**
```php
describe('ModelSchemaAdapter', function () {
    // tests...
})->group('migration', 'adapters');
```

Cette organisation permet de :
- ✅ Séparer clairement les anciens tests des nouveaux
- ✅ Exécuter seulement les tests de migration si nécessaire  
- ✅ Identifier rapidement les problèmes liés à la migration
- ✅ Faciliter le debugging et le développement incrémental
  - Registration des types personnalisés

#### Tests
- [x] **2.4** Tests unitaires pour tous les adaptateurs
  - [x] `tests/Unit/Adapters/ModelSchemaAdapterTest.php` (groupe: `migration`, `adapters`)
  - [x] `tests/Unit/Adapters/FragmentAdapterTest.php` (groupe: `migration`, `adapters`, `fragments`)
  - [x] `tests/Unit/Adapters/SchemaParserAdapterTest.php` (groupe: `migration`, `adapters`, `schema-parser`)
- [x] **2.5** Tests d'intégration Schema ↔ Fragments

### Phase 3: Migration du Schema Parser ✅ COMPLETED
#### Tâches
- [x] **3.1** Créer `SchemaParserAdapter` avec pattern de composition
- [x] **3.2** Modifier `TurboSchemaManager` pour utiliser `SchemaParserAdapter`
- [x] **3.3** Maintenir compatibilité avec méthodes `getAllSchemas()`, `exists()`, etc.
- [x] **3.4** Conserver les méthodes publiques pour compatibilité totale
- [x] **3.5** Support des types de retour nullable (`?Schema`) pour robustesse

**Status:** ✅ Phase complètement terminée avec succès
- `SchemaParserAdapter` créé avec composition (pas héritage)
- `TurboSchemaManager` utilise maintenant l'adaptateur
- Tests organisés avec groupes Pest 3 (`migration`, `adapters`, `schema-parser`)
- Tous les 128 tests passent, compatibilité 100% préservée

#### Tests
- [x] **3.6** Vérifier que tous les tests existants passent ✅ (128/128)
- [x] **3.7** Tests de l'adaptateur avec délégation et validation ✅ (4 tests)
- [x] **3.8** Tests avec différents scénarios (parse, parseArray, méthodes utilitaires) ✅

### Phase 4: Migration du TurboSchemaManager
#### Tâches
- [ ] **4.1** Remplacer `resolveSchema()` par l'approche fragments
- [ ] **4.2** Migrer `validateSchema()` vers le package externe
- [ ] **4.3** Adapter `parseFieldsShorthand()` pour utiliser les plugins
- [ ] **4.4** Migrer la création de fichiers schema
- [ ] **4.5** Adapter `listSchemas()` et `schemaExists()`

#### Tests
- [ ] **4.6** Tests de validation avec les nouveaux validateurs
- [ ] **4.7** Tests de création/parsing de schémas
- [ ] **4.8** Tests de compatibilité avec l'API existante

### Phase 5: Migration des Field Types
#### Tâches
- [ ] **5.1** Créer des plugins pour chaque field type TurboMaker
  - `src/FieldTypes/Plugins/StringFieldTypePlugin.php`
  - `src/FieldTypes/Plugins/IntegerFieldTypePlugin.php`
  - Etc. pour tous les 25+ types
  
- [ ] **5.2** Migrer `FieldTypeRegistry` vers `FieldTypePluginManager`
- [ ] **5.3** Adapter la validation des field types
- [ ] **5.4** Configurer l'auto-discovery des plugins

#### Tests
- [ ] **5.5** Tests pour chaque plugin de field type
- [ ] **5.6** Tests d'intégration avec le registry
- [ ] **5.7** Tests de validation des configurations

### Phase 6: Migration des Commandes
#### Tâches
- [ ] **6.1** Adapter `TurboSchemaCommand`
  - `list` → utiliser `SchemaService`
  - `create` → utiliser fragments + stubs
  - `show` → utiliser `SchemaService::parseAndSeparateSchema()`
  - `validate` → utiliser `SchemaService::validateCoreSchema()`
  
- [ ] **6.2** Adapter `TurboMakeCommand`
  - Utiliser `GenerationService::generateAll()`
  - Adapter `resolveSchema()` pour fragments
  - Conserver l'affichage des informations

#### Tests
- [ ] **6.3** Tests des commandes avec différents scénarios
- [ ] **6.4** Tests d'intégration bout-en-bout
- [ ] **6.5** Tests de performance des commandes

### Phase 7: Migration des Générateurs
#### Tâches
- [ ] **7.1** Adapter `BaseGenerator`
  - Méthodes pour récupérer les fragments
  - Interface unifiée pour tous les générateurs
  
- [ ] **7.2** Migrer chaque générateur individuellement
  - `ModelGenerator` → utiliser fragment model
  - `MigrationGenerator` → utiliser fragment migration
  - `RequestGenerator` → utiliser fragment requests
  - Etc.

- [ ] **7.3** Adapter les templates/stubs si nécessaire
- [ ] **7.4** Optimiser la génération avec les fragments

#### Tests
- [ ] **7.5** Tests de génération pour chaque type
- [ ] **7.6** Comparaison des fichiers générés (avant/après)
- [ ] **7.7** Tests de performance de génération

### Phase 8: Configuration et Service Provider
#### Tâches
- [ ] **8.1** Adapter `LaravelTurbomakerServiceProvider`
  - Registration des nouveaux services
  - Binding des adaptateurs
  - Configuration des plugins
  
- [ ] **8.2** Mettre à jour `config/turbomaker.php`
  - Configuration ModelSchema
  - Migration des anciennes configs
  - Documentation des nouvelles options

- [ ] **8.3** Gérer la rétrocompatibilité des configs

#### Tests
- [ ] **8.4** Tests de configuration
- [ ] **8.5** Tests du service provider
- [ ] **8.6** Tests de binding des services

---

## 🧪 Stratégie de Tests

### Tests de Régression
- [ ] **R.1** Tous les tests existants doivent passer
- [ ] **R.2** Génération identique pour les mêmes schemas
- [ ] **R.3** Compatibilité API publique maintenue
- [ ] **R.4** Performance égale ou supérieure

### Nouveaux Tests
- [ ] **N.1** Tests des adaptateurs
- [ ] **N.2** Tests d'intégration avec ModelSchema
- [ ] **N.3** Tests des nouveaux field types plugins
- [ ] **N.4** Tests de performance comparative

### Tests de Migration
- [ ] **M.1** Migration de schemas existants
- [ ] **M.2** Validation de schemas complexes
- [ ] **M.3** Tests avec gros volumes de données
- [ ] **M.4** Tests de cache et optimisation

---

## 📚 Documentation à Mettre à Jour

### README
- [ ] **D.1** Mise à jour des exemples d'utilisation
- [ ] **D.2** Documentation des nouvelles fonctionnalités
- [ ] **D.3** Guide de migration pour les utilisateurs
- [ ] **D.4** Exemples avec les nouveaux field types

### CHANGELOG
- [ ] **D.5** Documenter les breaking changes
- [ ] **D.6** Lister les nouvelles fonctionnalités
- [ ] **D.7** Guide de migration depuis v2.0

### Wiki
- [ ] **D.8** Nouveau guide des field types
- [ ] **D.9** Documentation des plugins personnalisés
- [ ] **D.10** Exemples d'utilisation avancée

---

## 🔄 Rétrocompatibilité

### Maintenir la Compatibilité
- [ ] **C.1** Toutes les méthodes publiques existantes
- [ ] **C.2** Format des fichiers `.schema.yml` existants
- [ ] **C.3** Configuration `turbomaker.php` existante
- [ ] **C.4** Commandes artisan inchangées

### Deprecations
- [ ] **C.5** Marquer l'ancien code comme deprecated
- [ ] **C.6** Ajouter des warnings pour les anciennes APIs
- [ ] **C.7** Planifier la suppression pour v4.0

---

## 🚀 Déploiement et Release

### Pre-Release
- [ ] **P.1** Tests intensifs sur projets réels
- [ ] **P.2** Benchmark de performance
- [ ] **P.3** Validation avec la communauté (beta)
- [ ] **P.4** Documentation finale

### Release v3.0
- [ ] **R.1** Tag de version
- [ ] **R.2** Publication sur Packagist
- [ ] **R.3** Annonce communauté
- [ ] **R.4** Mise à jour du README principal

---

## 📊 Métriques de Succès

### Performance
- [ ] Temps de parsing ≤ version actuelle
- [ ] Mémoire utilisée ≤ version actuelle
- [ ] Cache plus efficace (hit rate > 90%)

### Fonctionnalités
- [ ] Tous les field types supportés
- [ ] Nouveaux types extensibles via plugins
- [ ] Validation plus robuste
- [ ] Gestion diff de schemas

### Compatibilité
- [ ] 100% des tests existants passent
- [ ] API publique inchangée
- [ ] Schemas existants compatibles
- [ ] Migration transparente

---

## 🔧 Outils et Scripts

### Scripts de Migration
- [ ] **S.1** Script de validation des schemas existants
- [ ] **S.2** Script de conversion field types → plugins
- [ ] **S.3** Script de benchmark performance
- [ ] **S.4** Script de validation post-migration

### Outils de Debug
- [ ] **S.5** Outil de comparaison fragments avant/après
- [ ] **S.6** Outil de debug du cache
- [ ] **S.7** Outil de profiling des performances

---

## ⚠️ Risques et Mitigation

### Risques Identifiés
1. **Breaking changes non détectés** → Tests exhaustifs + beta
2. **Performance dégradée** → Benchmarks continus
3. **Complexité migration** → Migration progressive par phases
4. **Bugs dans le package externe** → Contribution/fix upstream

### Plan de Rollback
- [ ] **RB.1** Garder l'ancienne implémentation en parallèle
- [ ] **RB.2** Feature flag pour activer/désactiver
- [ ] **RB.3** Tests de rollback automatisés

---

## 📅 Timeline Estimé

- **Phase 1**: 1 jour
- **Phase 2**: 3-4 jours
- **Phase 3**: 2-3 jours
- **Phase 4**: 2-3 jours
- **Phase 5**: 4-5 jours
- **Phase 6**: 2-3 jours
- **Phase 7**: 5-6 jours
- **Phase 8**: 2 jours

**Total estimé**: 21-27 jours de développement

---

## ✅ Checklist de Validation Finale

### Avant Merge
- [ ] Tous les tests passent (100%)
- [ ] Performance ≥ version actuelle
- [ ] Documentation à jour
- [ ] Changelog complet
- [ ] Rétrocompatibilité vérifiée

### Après Merge
- [ ] CI/CD vert
- [ ] Tests sur projets réels
- [ ] Feedback communauté
- [ ] Monitoring performance

---

*Ce document sera mis à jour au fur et à mesure de l'avancement de la migration.*
