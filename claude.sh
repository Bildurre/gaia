#!/bin/bash

set -euo pipefail

ACTION="${1:-}"
BRANCH="${2:-}"
MAIN_BRANCH="main"

show_usage() {
  echo "Usage:"
  echo "  ./claude.sh --start claude/branch-name"
  echo "  ./claude.sh --finish claude/branch-name"
}

ensure_clean_working_tree() {
  if [ -n "$(git status --porcelain)" ]; then
    echo "You have uncommitted changes. Commit or stash them first."
    exit 1
  fi
}

if [ -z "$ACTION" ] || [ -z "$BRANCH" ]; then
  show_usage
  exit 1
fi

case "$ACTION" in
  --start)
    ensure_clean_working_tree
    echo "Fetching branch $BRANCH..."
    git fetch origin "$BRANCH"

    if git show-ref --verify --quiet "refs/heads/$BRANCH"; then
      git checkout "$BRANCH"
      git pull --ff-only origin "$BRANCH"
    else
      git checkout -b "$BRANCH" --track "origin/$BRANCH"
    fi

    echo "Done. You are on $BRANCH"
    ;;

  --finish)
    ensure_clean_working_tree
    echo "Merging $BRANCH into $MAIN_BRANCH..."
    git fetch origin "$BRANCH"
    git checkout "$MAIN_BRANCH"
    git pull --ff-only origin "$MAIN_BRANCH"

    if ! git merge --no-edit "origin/$BRANCH"; then
      echo "Merge conflict. Resolve it and commit, or run: git merge --abort"
      exit 1
    fi

    git push origin "$MAIN_BRANCH"

    echo "Deleting branch $BRANCH..."
    if git show-ref --verify --quiet "refs/heads/$BRANCH"; then
      if ! git branch -d "$BRANCH"; then
        echo "Local branch $BRANCH has commits that are not in $MAIN_BRANCH. Not deleted."
        exit 1
      fi
    fi
    git push origin --delete "$BRANCH"

    echo "Done. Branch $BRANCH merged and deleted."
    ;;

  *)
    echo "Unknown option: $ACTION"
    show_usage
    exit 1
    ;;
esac
