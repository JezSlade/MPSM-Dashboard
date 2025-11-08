#!/bin/bash

# Test device fetching to see where it stops

echo "=== TESTING DEVICE FETCH ==="
echo ""

total=0
page=1

while [ $page -le 10 ]; do
    echo "Fetching page $page..."

    response=$(curl -s -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
        -H "Content-Type: application/json" \
        -d "{\"action\":\"Device/List\",\"params\":{\"PageNumber\":$page,\"PageRows\":50,\"SortColumn\":\"Id\",\"SortOrder\":0}}")

    # Check success
    success=$(echo "$response" | grep -c '"success".*true')
    if [ "$success" -eq 0 ]; then
        echo "  ERROR: API call failed"
        echo "$response" | head -10
        break
    fi

    # Count devices
    count=$(echo "$response" | grep -o '"DealerId"' | wc -l | tr -d ' ')
    total=$((total + count))

    echo "  Got $count devices (Total: $total)"

    if [ "$count" -lt 50 ]; then
        echo "  Last page (< 50 devices)"
        break
    fi

    page=$((page + 1))
    sleep 0.1
done

echo ""
echo "Final: $total devices from $((page)) pages"
