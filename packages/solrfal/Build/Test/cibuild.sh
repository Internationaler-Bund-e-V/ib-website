#!/usr/bin/env bash

echo "PWD: $(pwd)"

TYPO3_PATH_WEB="$(pwd)/.Build/Web"
export TYPO3_PATH_WEB
TYPO3_PATH_PACKAGES="$(pwd)/.Build/vendor/"
export TYPO3_PATH_PACKAGES

TEST_SUITES_STATUS_CODE=0
# use from vendor dir

echo "Check compliance against TYPO3 Coding Standards"
if ! .Build/bin/php-cs-fixer --version > /dev/null 2>&1
then
  echo "TYPO3 https://github.com/TYPO3/coding-standards is not set properly."
  echo "Please fix that asap to avoid unwanted changes in the future."
  exit 1
else
  echo "TYPO3 Coding Standards compliance: See https://github.com/TYPO3/coding-standards"
  # @todo: Test multiple paths after each is applied and then the single call only
  #        Next: && composer t3:standards:fix -- --diff --verbose --dry-run Tests/Integration \
  #              && composer t3:standards:fix -- --diff --verbose --dry-run Classes
  if ! composer t3:standards:fix -- --diff --verbose --dry-run Tests/Unit
  then
    echo "Some files are not compliant to TYPO3 Coding Standards"
    echo "Please fix the files listed above."
    echo "Tip for auto fix: "
    echo "  composer install && composer exec php-cs-fixer fix"
    exit 1
  else
    echo "The code is TYPO3 Coding Standards compliant! Great job!"
  fi
fi
echo -e "\n\n"

echo "Run XML Lint"
if ! .Build/bin/xmllint --version > /dev/null 2>&1; then
  echo "XML Lint not found, skipping XML linting."
else
  echo "Check syntax of XML files"
  if ! composer exec xmllint -- Resources/Private/Language/ -p '*.xlf'
  then
    echo "Some XML files are not valid"
    echo "Please fix the files listed above"
    exit 1
  fi
fi
echo -e "\n\n"

echo "Run unit tests"
if ! composer tests:unit
then
    echo "Error during running the unit tests please check and fix them"
    TEST_SUITES_STATUS_CODE=1
fi
echo -e "\n\n"

echo "Check environment for integration tests"
#
# Map the travis and shell variable names to the expected
# casing of the TYPO3 core.
#
if [ -n "$TYPO3_DATABASE_NAME" ]; then
	export typo3DatabaseName=$TYPO3_DATABASE_NAME
else
	echo "No environment variable TYPO3_DATABASE_NAME set. Please set it to run the integration tests."
	exit 1
fi

if [ -n "$TYPO3_DATABASE_HOST" ]; then
	export typo3DatabaseHost=$TYPO3_DATABASE_HOST
else
	echo "No environment variable TYPO3_DATABASE_HOST set. Please set it to run the integration tests."
	exit 1
fi

if [ -n "$TYPO3_DATABASE_USERNAME" ]; then
	export typo3DatabaseUsername=$TYPO3_DATABASE_USERNAME
else
	echo "No environment variable TYPO3_DATABASE_USERNAME set. Please set it to run the integration tests."
	exit 1
fi

if [[ -v TYPO3_DATABASE_PASSWORD ]]; then # because empty password is possible
	export typo3DatabasePassword=$TYPO3_DATABASE_PASSWORD
else
	echo "No environment variable TYPO3_DATABASE_PASSWORD set. Please set it to run the integration tests."
	exit 1
fi

echo "Run integration tests"
if ! composer tests:integration
then
    echo "Error during running the integration tests please check and fix them"
    TEST_SUITES_STATUS_CODE=1
fi
echo -e "\n\n"

exit $TEST_SUITES_STATUS_CODE
