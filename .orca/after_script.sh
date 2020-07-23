#!/usr/bin/env bash

# NAME
#     after_script.sh - Perform final, post-script tasks.
#
# SYNOPSIS
#     after_script.sh
#
# DESCRIPTION
#     Logs the job on cron if telemetry is enabled.

cd "$(dirname "$0")" || exit
source ../../orca/bin/travis/_includes.sh

if [[ "${ORCA_COLLECT_COVERAGE}" == "TRUE" ]]; then
  composer -d"${TRAVIS_BUILD_DIR}" require --dev 'codacy/coverage:dev-master'
  "${TRAVIS_BUILD_DIR}/vendor/bin/codacycoverage" clover --git-commit ${TRAVIS_COMMIT} "${ORCA_COVERAGE_PATH}/clover.xml"
fi
