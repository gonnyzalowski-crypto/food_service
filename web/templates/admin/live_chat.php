<?php
$chatContractors = $chatContractors ?? [];
$selectedContractor = $selectedContractor ?? null;
$messages = $messages ?? [];
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Live Chat</h1>
    <p class="page-subtitle">Chat with contractors in real-time. Messages are retained for 7 days.</p>
  </div>
</div>

<div style="display: grid; grid-template-columns: 300px 1fr; gap: 24px; height: calc(100vh - 200px); min-height: 500px;">
  <!-- Contractor List -->
  <div class="card" style="overflow: hidden; display: flex; flex-direction: column;">
    <div class="card-header">
      <h3 class="card-title">Conversations</h3>
    </div>
    <div class="card-body" style="padding: 0; overflow-y: auto; flex: 1;">
      <?php if (empty($chatContractors)): ?>
        <div style="padding: 24px; color: rgba(255,255,255,0.5); text-align: center;">
          No active conversations.<br>
          <small>Contractors can start a chat from the Supply Portal.</small>
        </div>
      <?php else: ?>
        <?php foreach ($chatContractors as $c): ?>
          <a href="/admin/live-chat?contractor_id=<?= (int)$c['id'] ?>" 
             style="display: block; padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.1); text-decoration: none; color: inherit; <?= ($selectedContractor && $selectedContractor['id'] == $c['id']) ? 'background: rgba(0,191,255,0.1);' : '' ?>">
            <div style="display: flex; justify-content: space-between; align-items: start;">
              <div>
                <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($c['company_name']) ?></div>
                <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);"><?= htmlspecialchars($c['full_name']) ?></div>
              </div>
              <?php if (!empty($c['unread_count'])): ?>
                <span style="background: #00bfff; color: #000; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 10px;">
                  <?= (int)$c['unread_count'] ?>
                </span>
              <?php endif; ?>
            </div>
            <?php if (!empty($c['last_message_at'])): ?>
              <div style="font-size: 0.75rem; color: rgba(255,255,255,0.4); margin-top: 4px;">
                <?= date('M j, g:i A', strtotime($c['last_message_at'])) ?>
              </div>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Chat Window -->
  <div class="card" style="overflow: hidden; display: flex; flex-direction: column;">
    <?php if ($selectedContractor): ?>
      <div class="card-header" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div>
          <h3 class="card-title" style="margin: 0;"><?= htmlspecialchars($selectedContractor['company_name']) ?></h3>
          <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">
            <?= htmlspecialchars($selectedContractor['full_name']) ?> · <?= htmlspecialchars($selectedContractor['contractor_code']) ?>
          </div>
        </div>
      </div>
      
      <!-- Messages -->
      <div id="chatMessages" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px;">
        <?php if (empty($messages)): ?>
          <div style="text-align: center; color: rgba(255,255,255,0.5); padding: 40px;">
            No messages yet. Start the conversation!
          </div>
        <?php else: ?>
          <?php foreach ($messages as $msg): ?>
            <div style="display: flex; <?= $msg['sender'] === 'admin' ? 'justify-content: flex-end;' : 'justify-content: flex-start;' ?>">
              <div style="max-width: 70%; padding: 12px 16px; border-radius: 16px; <?= $msg['sender'] === 'admin' ? 'background: #00bfff; color: #000;' : 'background: rgba(255,255,255,0.1); color: #fff;' ?>">
                <div style="word-wrap: break-word;"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                <div style="font-size: 0.7rem; margin-top: 4px; opacity: 0.7;">
                  <?= date('M j, g:i A', strtotime($msg['created_at'])) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      
      <!-- Send Message Form -->
      <div style="border-top: 1px solid rgba(255,255,255,0.1); padding: 16px;">
        <form action="/admin/live-chat/send" method="POST" style="display: flex; gap: 12px;">
          <?= csrf_field() ?>
          <input type="hidden" name="contractor_id" value="<?= (int)$selectedContractor['id'] ?>">
          <input type="text" name="message" class="form-control" placeholder="Type your message..." style="flex: 1;" required autofocus>
          <button type="submit" class="btn btn-primary">Send</button>
        </form>
      </div>
    <?php else: ?>
      <div class="card-body" style="display: flex; align-items: center; justify-content: center; flex: 1;">
        <div style="text-align: center; color: rgba(255,255,255,0.5);">
          <div style="font-size: 3rem; margin-bottom: 16px;">💬</div>
          <div>Select a conversation to view messages</div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Auto-scroll to bottom of chat
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) {
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Auto-refresh every 10 seconds if a conversation is selected
<?php if ($selectedContractor): ?>
setInterval(() => {
  window.location.reload();
}, 30000);
<?php endif; ?>
</script>
