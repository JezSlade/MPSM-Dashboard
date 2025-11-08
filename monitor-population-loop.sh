#!/bin/bash

# Monitor and drive population to completion
# Loops until all devices are populated

echo "=== DEVICE POPULATION MONITOR ==="
echo ""

TARGET=8000
ATTEMPT=0
MAX_ATTEMPTS=100

while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    ATTEMPT=$((ATTEMPT + 1))
    echo "[$ATTEMPT] Checking progress..."

    # Get current count
    COUNT_JSON=$(curl -s --max-time 10 "https://mpsm.resolutionsbydesign.us/cms/api/quick-count.php" --user jez:4Zx7m9kP2qL5wN8tY1cV3bR6dF 2>&1)

    if [ $? -ne 0 ]; then
        echo "  ⚠️  Server timeout or unreachable"
        echo "  Waiting 30s for server to recover..."
        sleep 30
        continue
    fi

    # Parse JSON
    DEVICES=$(echo "$COUNT_JSON" | grep -o '"devices":[0-9]*' | grep -o '[0-9]*')
    DRILLDOWNS=$(echo "$COUNT_JSON" | grep -o '"drilldowns":[0-9]*' | grep -o '[0-9]*')

    if [ -z "$DEVICES" ]; then
        echo "  ⚠️  Could not parse count (server may be busy)"
        echo "  Response: $COUNT_JSON" | head -c 200
        echo ""
        echo "  Waiting 30s..."
        sleep 30
        continue
    fi

    PROGRESS=$((DEVICES * 100 / TARGET))
    echo "  📊 Devices: $DEVICES / $TARGET ($PROGRESS%)"
    echo "  📋 Drill-downs: $DRILLDOWNS"

    # Check if complete
    if [ "$DEVICES" -ge "$TARGET" ]; then
        echo ""
        echo "✅ DEVICE POPULATION COMPLETE!"
        echo "   Total devices: $DEVICES"
        echo "   Drill-downs: $DRILLDOWNS"
        exit 0
    fi

    # Check if stuck (no progress in last few attempts)
    if [ -f /tmp/last_device_count ]; then
        LAST_COUNT=$(cat /tmp/last_device_count)
        if [ "$DEVICES" -eq "$LAST_COUNT" ]; then
            echo "  ⚠️  No progress detected - running chunked populate..."

            # Run chunked populate
            CHUNK_RESULT=$(curl -s --max-time 300 "https://mpsm.resolutionsbydesign.us/cms/api/populate-chunked.php" --user jez:4Zx7m9kP2qL5wN8tY1cV3bR6dF 2>&1)

            if echo "$CHUNK_RESULT" | grep -q "CHUNK COMPLETE"; then
                echo "  ✅ Chunk processed successfully"
                ADDED=$(echo "$CHUNK_RESULT" | grep "Devices stored:" | grep -o '[0-9]*')
                echo "  Added $ADDED devices"
            else
                echo "  ⚠️  Chunk processing may have failed"
                echo "$CHUNK_RESULT" | head -10
            fi
        fi
    fi

    echo "$DEVICES" > /tmp/last_device_count

    echo "  Waiting 15s before next check..."
    sleep 15
    echo ""
done

echo "❌ Max attempts reached without completion"
echo "   Current: $DEVICES / $TARGET"
exit 1
