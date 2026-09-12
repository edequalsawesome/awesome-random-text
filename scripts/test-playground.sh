#!/usr/bin/env bash
set -euo pipefail

plugin_root=$(cd "$(dirname "$0")/.." && pwd)

for lane in '6.9 7.4' 'latest 8.3'; do
	read -r wp php <<< "$lane"
	npx --yes @wp-playground/cli@3.1.53 run-blueprint \
		--wp="$wp" \
		--php="$php" \
		--auto-mount="$plugin_root" \
		--blueprint="$plugin_root/tests/integration/blueprint.json" \
		--blueprint-may-read-adjacent-files \
		--verbosity=quiet
	echo "WP $wp / PHP $php Playground checks passed."
done
