#!/bin/bash
#
# v37.sh
#
# Project: BluebirdCRM
# Authors: Nathan Frank
# Organization: New York State Senate
# Date: 2024-12-04
#
# The purpose of this script is to execute a 1 time migration of custom activity data that is associated with
# Website Surveys (Questionnaires) per ticket NYSS 16799. The script does the following:
# 1. It takes all custom group and custom field survey data associated with Activities, formats the data as text and
#    places the data in the Activity.details field.
# 2. It also formats the data as JSON and puts it into the Website_Survey.survey_data field
# 3. It deactivates all custom data groups associated with Surveys
#
# There's no harm in running this script more than once on a database instance. But, there should be no need to.
# This is a "use it once and forget it" script.
#

prog=`basename $0`
script_dir=`dirname $0`
drush=$script_dir/drush.sh

if [ $# -ne 1 ]; then
  echo "Usage: $prog instanceName" >&2
  exit 1
fi
instance="$1"

# Migrate Data
echo "migrating custom survey fields to Activity details field..."
$drush $instance cvapi WebIntegration.migrateSurveyData version=4 checkPermissions=0

MIGRATION_STATUS=$?

# Deactivate Custom Survey Groups
if [ $MIGRATION_STATUS = 0 ]; then
  echo "deactivating survey custom data groups..."
  $drush $instance cvapi WebIntegration.updateSurveyGroups version=4 checkPermissions=0 active=0 public=0
fi