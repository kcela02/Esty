<?php
require_once __DIR__ . '/session_bootstrap.php';
require_once '../db.php';
requireAdminLogin();

// Fetch admin users
$admins = [];
$res = $conn->query("SELECT id, username, created_at FROM admin_users ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) {
    $admins[] = $row;
}

$flash = $_SESSION['admin_users_flash'] ?? null;
if ($flash !== null) {
    unset($_SESSION['admin_users_flash']);
}

$loggedInAdmin = $_SESSION['admin'] ?? null;

include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Admin User Management - Esty Admin</title>
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
  <link rel='stylesheet' href='admin-style.css'>
  <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'>
</head>
<body>
<div class='main-content'>
  <div class='card mb-4'>
    <div class='d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 p-3 pb-0'>
      <div>
        <h2 class='fw-bold mb-1'>Admin User Management</h2>
        <p class='muted mb-0'>Invite trusted teammates, adjust accounts, and keep credentials secure.</p>
      </div>
      <button class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#addAdminModal'>
        <i class='bi bi-person-plus me-1'></i> Add Admin
      </button>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class='alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mx-1 mx-md-0' role='alert'>
      <?= htmlspecialchars($flash['message']) ?>
      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>
  <?php endif; ?>

  <div class='card'>
    <div class='card-body'>
      <div class='table-responsive'>
        <table class='table table-hover align-middle mb-0'>
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Created</th>
              <th class='text-center'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($admins)): ?>
              <tr><td colspan='4' class='text-center text-muted py-4'>No admin users found yet. Use “Add Admin” to invite one.</td></tr>
            <?php else: foreach ($admins as $admin): ?>
              <?php $isSelf = ($loggedInAdmin !== null && $loggedInAdmin === $admin['username']); ?>
              <tr>
                <td><?= (int) $admin['id'] ?></td>
                <td>
                  <?= htmlspecialchars($admin['username']) ?>
                  <?php if ($isSelf): ?><span class='badge bg-light text-dark border ms-2'>You</span><?php endif; ?>
                </td>
                <td><?= $admin['created_at'] ? date('M d, Y', strtotime($admin['created_at'])) : '—' ?></td>
                <td class='text-center'>
                  <div class='d-flex flex-wrap gap-2 justify-content-center'>
                    <button
                      class='btn btn-sm btn-warning'
                      data-bs-toggle='modal'
                      data-bs-target='#editAdminModal'
                      data-id='<?= (int) $admin['id'] ?>'
                      data-username='<?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>'
                      data-self='<?= $isSelf ? '1' : '0' ?>'
                    >
                      <i class='bi bi-pencil-square me-1'></i>Edit
                    </button>
                    <button
                      class='btn btn-sm btn-danger'
                      data-bs-toggle='modal'
                      data-bs-target='#deleteAdminModal'
                      data-id='<?= (int) $admin['id'] ?>'
                      data-username='<?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>'
                      <?= $isSelf ? 'disabled title="You cannot delete your own account"' : '' ?>
                    >
                      <i class='bi bi-trash me-1'></i>Delete
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Admin Modal -->
<div class='modal fade' id='addAdminModal' tabindex='-1' aria-hidden='true'>
  <div class='modal-dialog'>
    <form class='modal-content' method='post' action='process_admin_user.php'>
      <input type='hidden' name='action' value='add'>
      <div class='modal-header'>
        <h5 class='modal-title'>Add Admin</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
      </div>
      <div class='modal-body'>
        <div class='mb-3'>
          <label class='form-label'>Username</label>
          <input type='text' name='username' class='form-control' required autofocus>
        </div>
        <div class='mb-3'>
          <label class='form-label'>Password <span class='text-muted'>(min. 8 characters)</span></label>
          <input type='password' name='password' class='form-control' minlength='8' required>
        </div>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
        <button type='submit' class='btn btn-success'>Add Admin</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Admin Modal -->
<div class='modal fade' id='editAdminModal' tabindex='-1' aria-hidden='true'>
  <div class='modal-dialog'>
    <form class='modal-content' method='post' action='process_admin_user.php'>
      <input type='hidden' name='action' value='edit'>
      <input type='hidden' name='id' id='editAdminId'>
      <div class='modal-header'>
        <h5 class='modal-title'>Edit Admin</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
      </div>
      <div class='modal-body'>
        <div class='mb-3'>
          <label class='form-label'>Username</label>
          <input type='text' name='username' id='editAdminUsername' class='form-control' required>
        </div>
        <div class='mb-3 d-none' id='editAdminCurrentPasswordGroup'>
          <label class='form-label'>Current Password <span class='text-muted'>(required when editing your own account)</span></label>
          <input type='password' name='current_password' id='editAdminCurrentPassword' class='form-control' autocomplete='current-password'>
        </div>
        <div class='row g-3'>
          <div class='col-md-6'>
            <label class='form-label'>New Password <span class='text-muted'>(leave blank to keep current)</span></label>
            <input type='password' name='password' id='editAdminPassword' class='form-control' minlength='8' autocomplete='new-password'>
          </div>
          <div class='col-md-6'>
            <label class='form-label'>Confirm New Password</label>
            <input type='password' name='confirm_password' id='editAdminConfirmPassword' class='form-control' minlength='8' autocomplete='new-password'>
          </div>
        </div>
        <small class='text-muted d-block mt-2'>Security tip: require current password when updating your own credentials.</small>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
        <button type='submit' class='btn btn-primary'>Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Admin Modal -->
<div class='modal fade' id='deleteAdminModal' tabindex='-1' aria-hidden='true'>
  <div class='modal-dialog'>
    <form class='modal-content' method='post' action='process_admin_user.php'>
      <input type='hidden' name='action' value='delete'>
      <input type='hidden' name='id' id='deleteAdminId'>
      <div class='modal-header'>
        <h5 class='modal-title text-danger'>Delete Admin</h5>
        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
      </div>
      <div class='modal-body'>
        <p id='deleteAdminMessage' class='mb-0'></p>
        <small class='text-muted d-block mt-2'>This action cannot be undone.</small>
      </div>
      <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
        <button type='submit' class='btn btn-danger'>Delete</button>
      </div>
    </form>
  </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
<script>
  const editAdminModal = document.getElementById('editAdminModal');
  if (editAdminModal) {
    editAdminModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;
      if (!button) return;
      const id = button.getAttribute('data-id');
      const username = button.getAttribute('data-username') || '';
      const isSelf = button.getAttribute('data-self') === '1';

      editAdminModal.querySelector('#editAdminId').value = id || '';
      editAdminModal.querySelector('#editAdminUsername').value = username;
      const passwordField = editAdminModal.querySelector('#editAdminPassword');
      if (passwordField) {
        passwordField.value = '';
      }
      const confirmField = editAdminModal.querySelector('#editAdminConfirmPassword');
      if (confirmField) {
        confirmField.value = '';
      }
      const currentGroup = editAdminModal.querySelector('#editAdminCurrentPasswordGroup');
      const currentField = editAdminModal.querySelector('#editAdminCurrentPassword');
      if (currentGroup && currentField) {
        if (isSelf) {
          currentGroup.classList.remove('d-none');
          currentField.value = '';
          currentField.required = true;
        } else {
          currentGroup.classList.add('d-none');
          currentField.value = '';
          currentField.required = false;
        }
      }
    });
  }

  const deleteAdminModal = document.getElementById('deleteAdminModal');
  if (deleteAdminModal) {
    deleteAdminModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;
      if (!button) return;
      const id = button.getAttribute('data-id');
      const username = button.getAttribute('data-username') || '';

      deleteAdminModal.querySelector('#deleteAdminId').value = id || '';
      const messageEl = deleteAdminModal.querySelector('#deleteAdminMessage');
      if (messageEl) {
        messageEl.textContent = `Are you sure you want to delete admin "${username}"?`;
      }
    });
  }
</script>
</body>
</html>
