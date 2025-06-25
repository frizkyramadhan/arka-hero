#!/bin/bash

# Employee Registration System - Email Tests Runner
# This script runs all email-related tests for the Employee Registration System

echo "🚀 Employee Registration System - Email Tests"
echo "=============================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to run a test file
run_test() {
    local test_file=$1
    local test_name=$2

    echo -e "${YELLOW}Running $test_name...${NC}"

    if php artisan test "$test_file" --stop-on-failure; then
        echo -e "${GREEN}✅ $test_name passed${NC}"
        echo ""
        return 0
    else
        echo -e "${RED}❌ $test_name failed${NC}"
        echo ""
        return 1
    fi
}

# Check if Laravel is available
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP is not installed or not in PATH${NC}"
    exit 1
fi

if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Laravel artisan file not found. Please run this script from the Laravel project root.${NC}"
    exit 1
fi

echo "📋 Test Summary:"
echo "- Unit Tests: EmployeeRegistrationInvitation Mailable"
echo "- Unit Tests: EmployeeRegistrationService Email Methods"
echo "- Feature Tests: Email Integration"
echo ""

# Initialize counters
total_tests=0
passed_tests=0

# Run Unit Tests
echo -e "${YELLOW}📦 UNIT TESTS${NC}"
echo "-------------"

# Test 1: EmployeeRegistrationInvitation Mailable
if run_test "tests/Unit/EmployeeRegistrationInvitationTest.php" "EmployeeRegistrationInvitation Mailable Tests"; then
    ((passed_tests++))
fi
((total_tests++))

# Test 2: EmployeeRegistrationService Email Methods
if run_test "tests/Unit/EmployeeRegistrationServiceEmailTest.php" "EmployeeRegistrationService Email Tests"; then
    ((passed_tests++))
fi
((total_tests++))

# Run Feature Tests
echo -e "${YELLOW}🔗 INTEGRATION TESTS${NC}"
echo "-------------------"

# Test 3: Email Integration
if run_test "tests/Feature/EmployeeRegistrationEmailIntegrationTest.php" "Email Integration Tests"; then
    ((passed_tests++))
fi
((total_tests++))

# Final Results
echo "=============================================="
echo -e "${YELLOW}📊 TEST RESULTS${NC}"
echo "=============================================="

if [ $passed_tests -eq $total_tests ]; then
    echo -e "${GREEN}🎉 ALL TESTS PASSED! ($passed_tests/$total_tests)${NC}"
    echo ""
    echo "✅ Email functionality is working correctly"
    echo "✅ Mailable class is properly configured"
    echo "✅ Service layer email methods are functional"
    echo "✅ Integration workflow is complete"
    echo ""
    echo "🚀 Your Employee Registration System email functionality is ready for production!"
    exit 0
else
    failed_tests=$((total_tests - passed_tests))
    echo -e "${RED}❌ SOME TESTS FAILED ($passed_tests/$total_tests passed, $failed_tests failed)${NC}"
    echo ""
    echo "Please check the test output above for details on failed tests."
    echo "Common issues:"
    echo "- Missing database migrations"
    echo "- Missing User factory"
    echo "- Incorrect route configuration"
    echo "- Missing dependencies"
    echo ""
    echo "Refer to tests/EMAIL_TESTING_DOCUMENTATION.md for troubleshooting help."
    exit 1
fi
