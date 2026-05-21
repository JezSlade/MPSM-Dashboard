#!/bin/bash

# Count ALL devices from API

total=0
page=1

echo "=== COUNTING ALL DEVICES FROM API ==="
echo ""

while [ $page -le 500 ]; do
    response=$(curl -s -X POST https://mpsm.resolutionsbydesign.us/mps-api/query \
        -H "Content-Type: application/json" \
        -d "{\"action\":\"Device/List\",\"params\":{\"PageNumber\":$page,\"PageRows\":50,\"SortColumn\":\"Id\",\"SortOrder\":0}}")

    # Check if API returned success
    success=$(echo "$response" | grep '"success"' | grep 'true')
    if [ -z "$success" ]; then
        echo "Page $page: API error or no response"
        echo "$response" | head -5
        break
    fi

    # Count devices in this page (count number of DealerId fields)
    count=$(echo "$response" | grep -o '"DealerId"' | wc -l | tr -d ' ')

    if [ "$count" -eq 0 ]; then
        echo "Page $page: No devices found - stopping"
        break
    fi

    total=$((total + count))

    if [ $((page % 10)) -eq 0 ] || [ "$count" -lt 50 ]; then
        echo "Page $page: $count devices (Total: $total)"
    fi

    if [ "$count" -lt 50 ]; then
        echo ""
        echo "Last page reached at page $page"
        break
    fi

    page=$((page + 1))
    sleep 0.1
done

echo ""
echo "======================================"
echo "TOTAL DEVICES IN API: $total"
echo "Pages fetched: $((page - 1))"
echo "======================================"
