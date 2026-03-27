#!/bin/bash
# Test rate limiting - should allow 5 requests per hour, block the 6th

API_URL="https://billoria-ad-api.ddev.site/api/v1/register"
FINGERPRINT="fedcba0987654321fedcba0987654321fedcba09"

for i in {31..36}; do
  echo "=== Request $((i-30)) of 6 ==="

  # Calculate proper 11-digit mobile number
  mobile=$(printf "880171234%04d" $i)

  result=$(curl -k -s -X POST "$API_URL" \
    -H "Content-Type: application/json" \
    -H "X-Client-Fingerprint: $FINGERPRINT" \
    -d "{
      \"accountType\": \"brand\",
      \"user\": {
        \"name\": \"RateTest$i\",
        \"email\": \"rate$i@testdomain.com\",
        \"password\": \"SecurePass123\",
        \"mobileNumber\": \"+${mobile}\"
        \"officialEmail\": \"rateorg$i@testdomain.com\"
      }
    }")

  echo "$result" | jq .
  echo ""
  sleep 1
done
