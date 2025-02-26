#!/bin/bash
set -e

# Get the running container ID for Magento
CONTAINER_ID=$(docker ps --filter "name=magento-container" --format "{{.ID}}")

# Run Magento setup commands inside the running container
docker exec -i $CONTAINER_ID php bin/magento setup:upgrade
docker exec -i $CONTAINER_ID php bin/magento setup:di:compile
docker exec -i $CONTAINER_ID php bin/magento cache:flush
docker exec -i $CONTAINER_ID php bin/magento indexer:reindex

echo "Magento setup completed successfully!"
