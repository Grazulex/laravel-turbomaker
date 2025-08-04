#!/bin/bash

# Phase 8 Cleanup Script - Remove Obsolete TurboMaker Field Types
# Run this after Phase 7 ModelSchema migration is complete and tested

echo "🧹 TurboMaker Phase 8 Cleanup - Removing Obsolete Field Types"
echo "=============================================================="

# Check if we're in the right directory
if [ ! -f "composer.json" ] || [ ! -d "src/Schema/FieldTypes" ]; then
    echo "❌ Error: Run this script from the TurboMaker root directory"
    exit 1
fi

# Backup before deletion (optional)
echo "📦 Creating backup of FieldTypes directory..."
if [ -d "src/Schema/FieldTypes" ]; then
    tar -czf "backup-fieldtypes-$(date +%Y%m%d-%H%M%S).tar.gz" src/Schema/FieldTypes/
    echo "✅ Backup created: backup-fieldtypes-$(date +%Y%m%d-%H%M%S).tar.gz"
fi

# List files to be removed
echo ""
echo "🗑️  Files to be removed (obsolete field types):"
echo "----------------------------------------------"
find src/Schema/FieldTypes/ -name "*.php" | sort

# Confirmation
echo ""
read -p "❓ Do you want to proceed with deletion? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Remove the entire FieldTypes directory
    echo "🗑️  Removing obsolete field types..."
    rm -rf src/Schema/FieldTypes/
    
    if [ $? -eq 0 ]; then
        echo "✅ Successfully removed src/Schema/FieldTypes/ directory"
    else
        echo "❌ Error removing directory"
        exit 1
    fi
    
    # Run composer dump-autoload to clean up autoloader
    echo ""
    echo "🔄 Updating composer autoloader..."
    composer dump-autoload
    
    # Run tests to ensure nothing is broken
    echo ""
    echo "🧪 Running tests to verify cleanup..."
    composer test
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "🎉 Phase 8 Cleanup Complete!"
        echo "✅ All obsolete field types removed"
        echo "✅ Tests passing"
        echo "✅ System ready for Phase 9 (optional)"
    else
        echo ""
        echo "⚠️  Cleanup complete but tests failed"
        echo "🔍 Review test output above"
        echo "📝 Check MODELSCHEMA_MIGRATION_ANALYSIS.md for known issues"
    fi
else
    echo "❌ Cleanup cancelled"
    exit 1
fi

echo ""
echo "📋 Next Steps:"
echo "1. Review MODELSCHEMA_MIGRATION_ANALYSIS.md"
echo "2. Update documentation if needed"
echo "3. Consider Phase 9 optimizations (optional)"
echo ""
echo "🏗️  Current Architecture:"
echo "   TurboMaker CLI → ModuleGenerator → ModelSchemaGenerationAdapter → ModelSchema Enterprise"
echo ""
