#!/bin/bash

# Script pour créer une release Laravel Arc
# Usage: ./release.sh 1.2.0 "Description des changements"

set -e

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher un message coloré
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Vérifier les arguments
if [ $# -lt 1 ]; then
    print_message $RED "❌ Usage: $0 <version> [release_notes]"
    print_message $YELLOW "💡 Exemple: $0 1.2.0 \"Ajout des traits pour DTOs\""
    exit 1
fi

VERSION=$1
RELEASE_NOTES=${2:-""}

# Vérifier que la version suit le format semver
if ! [[ $VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    print_message $RED "❌ La version doit suivre le format semver (ex: 1.2.0)"
    exit 1
fi

# Vérifier que gh CLI est installé
if ! command -v gh &> /dev/null; then
    print_message $RED "❌ GitHub CLI (gh) n'est pas installé"
    print_message $YELLOW "💡 Installez-le avec: sudo apt install gh (Ubuntu) ou brew install gh (macOS)"
    exit 1
fi

# Vérifier que l'utilisateur est connecté à GitHub
if ! gh auth status &> /dev/null; then
    print_message $RED "❌ Vous n'êtes pas connecté à GitHub"
    print_message $YELLOW "💡 Connectez-vous avec: gh auth login"
    exit 1
fi

print_message $BLUE "🚀 Création de la release v$VERSION..."

# Vérifier l'état du repository
if [ -n "$(git status --porcelain)" ]; then
    print_message $YELLOW "⚠️  Il y a des changements non committés:"
    git status --short
    read -p "Voulez-vous continuer ? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_message $RED "❌ Release annulée"
        exit 1
    fi
fi

# Vérifier que nous sommes sur la branche main
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    print_message $YELLOW "⚠️  Vous n'êtes pas sur la branche main (actuellement sur: $CURRENT_BRANCH)"
    read -p "Voulez-vous continuer ? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_message $RED "❌ Release annulée"
        exit 1
    fi
fi

# Vérifier que le tag n'existe pas déjà
if git rev-parse "v$VERSION" >/dev/null 2>&1; then
    print_message $RED "❌ Le tag v$VERSION existe déjà"
    exit 1
fi

# Pousser les derniers changements
print_message $BLUE "📤 Push des derniers changements..."
git push origin $CURRENT_BRANCH

# Déclencher le workflow GitHub Actions
print_message $BLUE "🎯 Déclenchement du workflow GitHub Actions..."

# Créer le payload JSON pour le workflow
if [ -n "$RELEASE_NOTES" ]; then
    gh workflow run release.yml \
        --field version="$VERSION" \
        --field release_notes="$RELEASE_NOTES"
else
    gh workflow run release.yml \
        --field version="$VERSION"
fi

print_message $GREEN "✅ Workflow déclenché avec succès !"
print_message $BLUE "🔍 Vous pouvez suivre le progress ici :"
print_message $BLUE "   https://github.com/Grazulex/laravel-devtoolbox/actions/workflows/release.yml"

# Attendre un peu et vérifier le statut
sleep 3
print_message $BLUE "📊 Statut du workflow :"
gh run list --workflow=release.yml --limit=1

print_message $GREEN "🎉 Release v$VERSION en cours de création !"
print_message $YELLOW "💡 Une fois terminé, la release sera disponible sur :"
print_message $YELLOW "   - GitHub: https://github.com/Grazulex/laravel-devtoolbox/releases"
print_message $YELLOW "   - Packagist: https://packagist.org/packages/grazulex/laravel-devtoolbox"
