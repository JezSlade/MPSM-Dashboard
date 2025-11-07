<?php
/**
 * User Repository
 * Handles user authentication and user data
 */

class UserRepository extends BaseRepository
{
    protected string $table = 'mpsm_users';
    protected string $primaryKey = 'id';
    protected int $cacheTtl = 3600; // 1 hour

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        $cacheKey = $this->getCacheKey('username', $username);

        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE username = ?
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        // Cache result
        $this->cache->set($cacheKey, $result, $this->cacheTtl);

        return $result;
    }

    /**
     * Verify user password
     */
    public function verifyPassword(string $username, string $password): bool
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return false;
        }

        return password_verify($password, $user['password']);
    }

    /**
     * Create user with hashed password
     */
    public function create(array $data): array
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        return parent::create($data);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET password = ?
            WHERE id = ?
        ");

        $result = $stmt->execute([$hashedPassword, $userId]);

        // Invalidate cache
        if ($result) {
            $this->cache->delete($this->getCacheKey('item', $userId));
        }

        return $result;
    }

    /**
     * Get user preferences
     */
    public function getPreferences(int $userId): ?array
    {
        $cacheKey = $this->getCacheKey('preferences', $userId);

        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $stmt = $this->pdo->prepare("
            SELECT preferences
            FROM mpsm_user_preferences
            WHERE user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $preferences = json_decode($row['preferences'], true);

        // Cache result
        $this->cache->set($cacheKey, $preferences, $this->cacheTtl);

        return $preferences;
    }

    /**
     * Save user preferences
     */
    public function savePreferences(int $userId, array $preferences): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO mpsm_user_preferences (user_id, preferences, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                preferences = VALUES(preferences),
                updated_at = NOW()
        ");

        $result = $stmt->execute([$userId, json_encode($preferences)]);

        // Invalidate cache
        if ($result) {
            $this->cache->delete($this->getCacheKey('preferences', $userId));
        }

        return $result;
    }

    /**
     * Track user visit
     */
    public function trackVisit(int $userId, string $username, string $pageUrl): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO mpsm_visitor_log
            (user_id, username, ip_address, user_agent, page_url, visited_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId,
            $username,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $pageUrl,
        ]);
    }
}
