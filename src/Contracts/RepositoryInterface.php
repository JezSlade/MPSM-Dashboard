<?php
/**
 * Repository Interface
 * Base contract for all repository classes
 *
 * Repositories abstract data access logic and provide a consistent API
 * for interacting with data sources (database, cache, API, etc.)
 */

interface RepositoryInterface
{
    /**
     * Find all records matching the given filters
     *
     * @param array $filters Key-value pairs for filtering
     * @return array Array of records
     */
    public function findAll(array $filters = []): array;

    /**
     * Find a single record by ID
     *
     * @param mixed $id The primary key value
     * @return array|null Record data or null if not found
     */
    public function findById($id): ?array;

    /**
     * Create a new record
     *
     * @param array $data Record data
     * @return array Created record with ID
     */
    public function create(array $data): array;

    /**
     * Update an existing record
     *
     * @param mixed $id The primary key value
     * @param array $data Updated data
     * @return array Updated record
     */
    public function update($id, array $data): array;

    /**
     * Delete a record
     *
     * @param mixed $id The primary key value
     * @return bool Success status
     */
    public function delete($id): bool;
}
