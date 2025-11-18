<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1>💬 Messagerie</h1>
        <p>Communications internes de l'équipe</p>
    </div>
    <div>
        <a href="/messages/compose" class="btn btn-primary">
            <i class="icon">✉️</i> Nouveau message
        </a>
    </div>
</div>

<?php if ($unreadCount > 0): ?>
    <div class="alert alert-info">
        <strong>📬 Vous avez <?= $unreadCount ?> message(s) non lu(s)</strong>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($conversations)): ?>
            <div class="empty-state">
                <p><strong>Aucune conversation</strong></p>
                <p>Commencez par envoyer votre premier message.</p>
                <a href="/messages/compose" class="btn btn-primary mt-2">Nouveau message</a>
            </div>
        <?php else: ?>
            <div class="conversations-list">
                <?php foreach ($conversations as $conv): ?>
                    <?php
                        $isUnread = !$conv['read_at'] && $conv['recipient_id'] == $_SESSION['user']['id'];
                        $otherUserId = $conv['sender_id'] == $_SESSION['user']['id'] ? $conv['recipient_id'] : $conv['sender_id'];
                        $otherUserName = $conv['sender_id'] == $_SESSION['user']['id'] ? $conv['recipient_name'] : $conv['sender_name'];
                    ?>
                    <a href="/messages/conversation/<?= $otherUserId ?>" class="conversation-item <?= $isUnread ? 'unread' : '' ?>">
                        <div class="conversation-avatar">
                            <?= strtoupper(substr($otherUserName, 0, 1)) ?>
                        </div>
                        <div class="conversation-content">
                            <div class="conversation-header">
                                <strong><?= htmlspecialchars($otherUserName) ?></strong>
                                <span class="conversation-time">
                                    <?= date('d/m/Y H:i', strtotime($conv['created_at'])) ?>
                                </span>
                            </div>
                            <?php if ($conv['subject']): ?>
                                <div class="conversation-subject">
                                    <?= htmlspecialchars($conv['subject']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="conversation-preview">
                                <?= htmlspecialchars(substr($conv['message'], 0, 100)) ?>
                                <?= strlen($conv['message']) > 100 ? '...' : '' ?>
                            </div>
                            <?php if ($conv['chantier_id']): ?>
                                <div class="conversation-tag">
                                    🏗️ <?= htmlspecialchars($conv['chantier_name'] ?? 'Chantier #' . $conv['chantier_id']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($isUnread): ?>
                            <div class="unread-badge"></div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.conversations-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.conversation-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    text-decoration: none;
    color: inherit;
    position: relative;
    transition: background-color 0.2s;
}

.conversation-item:hover {
    background-color: #f9fafb;
}

.conversation-item.unread {
    background-color: #eff6ff;
}

.conversation-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.conversation-content {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.conversation-time {
    font-size: 0.875rem;
    color: #6b7280;
}

.conversation-subject {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.conversation-preview {
    color: #6b7280;
    font-size: 0.875rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.conversation-tag {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-top: 0.5rem;
}

.unread-badge {
    width: 12px;
    height: 12px;
    background: #2563eb;
    border-radius: 50%;
    position: absolute;
    top: 1.5rem;
    right: 1rem;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
