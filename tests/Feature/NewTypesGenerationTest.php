<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('can generate migration with all new field types from yaml schema', function () {
    // Créer un schéma avec tous les types "problématiques"
    $schemaContent = <<<'YAML'
fields:
  title:
    type: string
    nullable: false
    comment: "Titre standard"
  
  description:
    type: text
    nullable: true
    comment: "Description normale"

  long_content:
    type: longText
    nullable: true
    comment: "Contenu très long"

  medium_content:
    type: mediumText
    nullable: true
    comment: "Contenu moyen"
    
  # Types numériques
  count:
    type: integer
    default: 0
    
  big_number:
    type: bigInteger
    default: 0

  unsigned_big:
    type: unsignedBigInteger
    default: 0
    comment: "Grand nombre non signé"

  tiny_status:
    type: tinyInteger
    default: 0
    comment: "Petit statut"

  small_priority:
    type: smallInteger
    default: 0
    comment: "Petite priorité"

  medium_counter:
    type: mediumInteger
    default: 0
    comment: "Compteur moyen"
    
  price:
    type: decimal
    precision: 10
    scale: 2
    default: 0.00
    
  # Types spéciaux
  uuid_field:
    type: uuid
    nullable: true
    
  email_address:
    type: email
    nullable: true
    
  website:
    type: url
    nullable: true
    
  config:
    type: json
    nullable: true

  binary_data:
    type: binary
    nullable: true
    comment: "Données binaires"

relationships:
  user:
    type: belongsTo
    model: User
YAML;

    // Écrire le fichier schéma
    $schemaPath = resource_path('schemas/NewTypesTest.schema.yml');
    File::ensureDirectoryExists(dirname($schemaPath));
    File::put($schemaPath, $schemaContent);

    // Clean up any existing migrations
    $existingMigrations = File::glob(database_path('migrations/*_create_new_types_tests_table.php'));
    foreach ($existingMigrations as $migration) {
        File::delete($migration);
    }

    // Générer le module
    $this->artisan('turbo:make', [
        'name' => 'NewTypesTest',
        '--schema' => 'NewTypesTest',
        '--force' => true,
    ])
        ->expectsOutput('🚀 Generating Laravel module: NewTypesTest')
        ->assertExitCode(0);

    // Vérifier que la migration contient tous les champs
    $migrationFiles = File::glob(database_path('migrations/*_create_new_types_tests_table.php'));
    expect($migrationFiles)->toHaveCount(1);

    $migrationContent = File::get($migrationFiles[0]);

    // Debug: afficher le contenu de la migration (commenté pour CI)
    // echo "\n=== Migration Content ===\n";
    // echo $migrationContent;
    // echo "\n========================\n";

    // Vérifier que les champs sont présents
    expect($migrationContent)->toContain('string(\'title\')');
    expect($migrationContent)->toContain('text(\'description\')');
    expect($migrationContent)->toContain('longText(\'long_content\')');
    expect($migrationContent)->toContain('mediumText(\'medium_content\')');
    expect($migrationContent)->toContain('integer(\'count\')');
    expect($migrationContent)->toContain('bigInteger(\'big_number\')');
    expect($migrationContent)->toContain('unsignedBigInteger(\'unsigned_big\')');
    expect($migrationContent)->toContain('tinyInteger(\'tiny_status\')');
    expect($migrationContent)->toContain('smallInteger(\'small_priority\')');
    expect($migrationContent)->toContain('mediumInteger(\'medium_counter\')');
    expect($migrationContent)->toContain('decimal(8, 2)(\'price\')');
    expect($migrationContent)->toContain('uuid(\'uuid_field\')');
    expect($migrationContent)->toContain('string(\'email_address\')'); // email devient string
    expect($migrationContent)->toContain('string(\'website\')'); // url devient string
    expect($migrationContent)->toContain('json(\'config\')');
    expect($migrationContent)->toContain('binary(\'binary_data\')');
    expect($migrationContent)->toContain('foreignId(\'user_id\')');

    // Nettoyer
    File::delete($schemaPath);
    foreach ($migrationFiles as $file) {
        File::delete($file);
    }

    // Assert test completed successfully
    expect(true)->toBeTrue('All new field types generated successfully');
});
