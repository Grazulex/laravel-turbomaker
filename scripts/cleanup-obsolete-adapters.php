<?php

declare(strict_types=1);

/**
 * Script de nettoyage des adaptateurs obsolètes
 *
 * Ce script supprime tous les adaptateurs qui sont devenus obsolètes
 * suite à la découverte que ModelSchema est un framework complet.
 */
$filesToDelete = [
    // Adaptateurs obsolètes
    'src/Adapters/ModelSchemaAdapter.php',
    'src/Adapters/FragmentAdapter.php',
    'src/Adapters/FieldTypeAdapter.php',
    'src/Adapters/SchemaParserAdapter.php',
    'src/Adapters/TurboSchemaManagerAdapter.php',

    // Tests des adaptateurs obsolètes
    'tests/Unit/Adapters/ModelSchemaAdapterTest.php',
    'tests/Unit/Adapters/ModelSchemaAdapterTest.php.backup',
    'tests/Unit/Adapters/FragmentAdapterTest.php',
    'tests/Unit/Adapters/FieldTypeAdapterTest.php',
    'tests/Unit/Adapters/SchemaParserAdapterTest.php',
    'tests/Unit/Adapters/TurboSchemaManagerAdapterTest.php',
    'tests/Unit/EnhancedTurboSchemaManagerTest.php',
];

$deletedCount = 0;
$errors = [];

echo "🗑️  NETTOYAGE DES ADAPTATEURS OBSOLÈTES\n";
echo "=====================================\n\n";

foreach ($filesToDelete as $file) {
    $fullPath = __DIR__.'/../'.$file;

    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            echo "✅ Supprimé: $file\n";
            $deletedCount++;
        } else {
            echo "❌ Erreur lors de la suppression: $file\n";
            $errors[] = $file;
        }
    } else {
        echo "⚠️  Fichier non trouvé: $file\n";
    }
}

// Supprimer le dossier Adapters s'il est vide
$adaptersDir = __DIR__.'/../src/Adapters';
if (is_dir($adaptersDir)) {
    $files = scandir($adaptersDir);
    $files = array_diff($files, ['.', '..']);

    if (empty($files)) {
        if (rmdir($adaptersDir)) {
            echo "✅ Dossier Adapters supprimé (vide)\n";
        }
    } else {
        echo '⚠️  Dossier Adapters conservé (contient encore: '.implode(', ', $files).")\n";
    }
}

// Supprimer le dossier tests/Unit/Adapters s'il est vide
$testsAdaptersDir = __DIR__.'/../tests/Unit/Adapters';
if (is_dir($testsAdaptersDir)) {
    $files = scandir($testsAdaptersDir);
    $files = array_diff($files, ['.', '..']);

    if (empty($files)) {
        if (rmdir($testsAdaptersDir)) {
            echo "✅ Dossier tests/Unit/Adapters supprimé (vide)\n";
        }
    } else {
        echo '⚠️  Dossier tests/Unit/Adapters conservé (contient encore: '.implode(', ', $files).")\n";
    }
}

echo "\n📊 RÉSUMÉ:\n";
echo "- Fichiers supprimés: $deletedCount\n";
echo '- Erreurs: '.count($errors)."\n";

if (! empty($errors)) {
    echo "\n❌ Fichiers en erreur:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n🚀 Nettoyage terminé !\n";
echo "Vous pouvez maintenant procéder au remplacement direct par ModelSchema.\n";
