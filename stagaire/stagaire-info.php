<?php
session_start();
// Redirect guests to login
if (empty($_SESSION['user_id'])) {
  header('Location: ../auth/index.php');
  exit;
}

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName  = trim($_POST['first_name'] ?? '');
    $lastName   = trim($_POST['last_name'] ?? '');
    $branch     = $_POST['branch'] ?? '';
  $university = trim($_POST['university'] ?? '');
  $speciality = trim($_POST['speciality'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $phone      = trim($_POST['phone'] ?? '');

    $validBranches = ['CP2K','CP1K','GL1K','RA1K','RA2K','GL1Z','GL2Z','GP1Z','GNL'];

    if (empty($firstName))  $errors[] = 'First name is required.';
    if (empty($lastName))   $errors[] = 'Last name is required.';
    // National ID text field removed — replaced by uploaded ID file
    if (!in_array($branch, $validBranches)) $errors[] = 'Please select a valid branch.';

    if (empty($university)) $errors[] = 'University is required.';
    if (empty($speciality)) $errors[] = 'Speciality is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';

    // File validations: national_id_card, self_photo, birth_certificate
    $filesExpected = ['national_id_card','self_photo','birth_certificate'];
    $allowedTypes = ['image/jpeg','image/png','image/webp'];
    foreach ($filesExpected as $f) {
      if (!isset($_FILES[$f]) || $_FILES[$f]['error'] !== UPLOAD_ERR_OK) {
        $errors[] = ucfirst(str_replace('_',' ', $f)) . ' is required.';
        continue;
      }
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mimeType = finfo_file($finfo, $_FILES[$f]['tmp_name']);
      finfo_close($finfo);
      if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = ucfirst(str_replace('_',' ', $f)) . ' must be JPEG, PNG, or WebP.';
      }
      if ($_FILES[$f]['size'] > 5 * 1024 * 1024) {
        $errors[] = ucfirst(str_replace('_',' ', $f)) . ' must be under 5 MB.';
      }
    }

    if (empty($errors)) {
      // Persist to database and move uploaded files
      require_once __DIR__ . '/../include/db.php';
      try {
        $conn->beginTransaction();
        $userId = $_SESSION['user_id'] ?? null;
        $stmt = $conn->prepare("INSERT INTO stagaires (user_id, first_name, last_name, branch, university, speciality, email, phone, created_at)
          VALUES (:user_id, :first_name, :last_name, :branch, :university, :speciality, :email, :phone, NOW())");
        $stmt->execute([
          ':user_id' => $userId,
          ':first_name' => $firstName,
          ':last_name' => $lastName,
          ':branch' => $branch,
          ':university' => $university,
          ':speciality' => $speciality,
          ':email' => $email,
          ':phone' => $phone,
        ]);
        $stagaireId = $conn->lastInsertId();

        // Prepare storage directory
        $photosBase = __DIR__ . '/../photos/stagaires';
        if (!is_dir($photosBase)) mkdir($photosBase, 0755, true);
        $targetDir = $photosBase . '/' . $stagaireId;
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $savedPaths = [null, null, null];
        $fileFields = ['national_id_card','self_photo','birth_certificate'];
        foreach ($fileFields as $i => $field) {
          if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $orig = $_FILES[$field]['name'];
            $ext = pathinfo($orig, PATHINFO_EXTENSION);
            $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
            $safe = strtolower(preg_replace('/[^a-z0-9_-]/i', '_', pathinfo($orig, PATHINFO_FILENAME)));
            $newName = $field . '_' . uniqid() . ($ext ? '.' . $ext : '');
            $dest = $targetDir . '/' . $newName;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
              // store relative web path
              $savedPaths[$i] = 'photos/stagaires/' . $stagaireId . '/' . $newName;
            }
          }
        }

        $upd = $conn->prepare("UPDATE stagaires SET national_id_card_path = :a, self_photo_path = :b, birth_certificate_path = :c WHERE id = :id");
        $upd->execute([':a' => $savedPaths[0], ':b' => $savedPaths[1], ':c' => $savedPaths[2], ':id' => $stagaireId]);

        $conn->commit();
        $success = true;
      } catch (Exception $ex) {
        if ($conn && $conn->inTransaction()) $conn->rollBack();
        $errors[] = 'Failed to save profile: ' . $ex->getMessage();
      }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Sonatrach — Stagaire Information</title>
<link rel="stylesheet" href="stagaire-info.css"/>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="../client/index.php" class="nav-logo">SONATRACH</a>
</nav>

<!-- HERO STRIP -->
<div class="hero-strip">
  <span class="badge">◆ AFRICA'S LARGEST ENERGY COMPANY</span>
  <h1 class="hero-title">Your <span class="gold">Information</span></h1>
  <p class="hero-sub">Complete your stagaire profile to proceed with your application.</p>
</div>

<!-- MAIN CONTENT -->
<main class="main-content">

  <?php if ($success): ?>
  <div class="alert alert-success">
    <span class="alert-icon">✔</span>
    <div>
      <strong>Profile submitted successfully!</strong><br/>
      Your information has been received. We will be in touch shortly.
    </div>
  </div>
  <?php elseif (!empty($errors)): ?>
  <div class="alert alert-error">
    <span class="alert-icon">✖</span>
    <div>
      <strong>Please fix the following errors:</strong>
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>

  <div class="form-card">
    <div class="form-card-header">
      <div class="form-card-accent"></div>
      <h2>Stagaire Profile</h2>
      <p>All fields are mandatory</p>
    </div>

    <form method="POST" enctype="multipart/form-data" novalidate>

      <!-- Name row -->
      <div class="field-row">
        <div class="field-group">
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" name="first_name"
                 placeholder="e.g. Yacine"
                 value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                 required/>
        </div>
        <div class="field-group">
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" name="last_name"
                 placeholder="e.g. Benali"
                 value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                 required/>
        </div>
      </div>

      <!-- University / Speciality (text inputs) -->
      <div class="field-row">
        <div class="field-group">
          <label for="university">University</label>
          <input type="text" id="university" name="university" placeholder="e.g. University of Algiers" value="<?= htmlspecialchars($_POST['university'] ?? '') ?>" required />
        </div>
        <div class="field-group">
          <label for="speciality">Speciality</label>
          <input type="text" id="speciality" name="speciality" placeholder="e.g. Petroleum Engineering" value="<?= htmlspecialchars($_POST['speciality'] ?? '') ?>" required />
        </div>
      </div>

      <!-- Contact: Email / Phone -->
      <div class="field-row">
        <div class="field-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
        </div>
        <div class="field-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" placeholder="e.g. +213600000000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required />
        </div>
      </div>

      <!-- Branch -->
      <div class="field-group full">
        <label for="branch">Branch</label>
        <div class="select-wrapper">
          <select id="branch" name="branch" required>
            <option value="" disabled <?= empty($_POST['branch']) ? 'selected' : '' ?>>— Select your branch —</option>
            <?php
            $branches = ['CP2K','CP1K','GL1K','RA1K','RA2K','GL1Z','GL2Z','GP1Z','GNL'];
            foreach ($branches as $b) {
                $sel = (($_POST['branch'] ?? '') === $b) ? 'selected' : '';
                echo "<option value=\"$b\" $sel>$b</option>";
            }
            ?>
          </select>
          <span class="select-arrow">▾</span>
        </div>
      </div>

      <!-- File uploads: National ID card, Self Photo, Birth Certificate -->
      <div class="field-row">
        <div class="field-group">
          <label>National ID Card</label>
          <div class="upload-zone small" id="uploadZoneID">
            <div class="upload-icon">🪪</div>
            <p class="upload-main">Drop ID card</p>
            <p class="upload-sub">or <label for="national_id_card" class="upload-link">browse</label></p>
            <input type="file" id="national_id_card" name="national_id_card" accept="image/jpeg,image/png,image/webp" hidden/>
            <div class="preview-wrap" id="previewID" style="display:none;"><img id="previewIDImg" src="#" alt="Preview"/><button type="button" class="remove-photo" data-target="national_id_card">✕</button></div>
          </div>
        </div>
        <div class="field-group">
          <label>Self Photo</label>
          <div class="upload-zone small" id="uploadZoneSelf">
            <div class="upload-icon">📷</div>
            <p class="upload-main">Drop self photo</p>
            <p class="upload-sub">or <label for="self_photo" class="upload-link">browse</label></p>
            <input type="file" id="self_photo" name="self_photo" accept="image/jpeg,image/png,image/webp" hidden/>
            <div class="preview-wrap" id="previewSelf" style="display:none;"><img id="previewSelfImg" src="#" alt="Preview"/><button type="button" class="remove-photo" data-target="self_photo">✕</button></div>
          </div>
        </div>
      </div>
      <div class="field-group full">
        <label>Birth Certificate</label>
        <div class="upload-zone small" id="uploadZoneBirth">
          <div class="upload-icon">📄</div>
          <p class="upload-main">Drop birth certificate</p>
          <p class="upload-sub">or <label for="birth_certificate" class="upload-link">browse</label></p>
          <input type="file" id="birth_certificate" name="birth_certificate" accept="image/jpeg,image/png,image/webp" hidden/>
          <div class="preview-wrap" id="previewBirth" style="display:none;"><img id="previewBirthImg" src="#" alt="Preview"/><button type="button" class="remove-photo" data-target="birth_certificate">✕</button></div>
        </div>
      </div>

      <!-- Submit -->
      <div class="form-footer">
        <button type="submit" class="btn-submit">
          <span>Submit Profile</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
        <p class="form-note">Your data is handled in accordance with Sonatrach's privacy policy.</p>
      </div>

    </form>
  </div>
</main>

<!-- STATS STRIP -->
<div class="stats-strip">
  <div class="stat"><span class="stat-num">60+</span><span class="stat-label">Years of Operation</span></div>
  <div class="stat-divider"></div>
  <div class="stat"><span class="stat-num">120K</span><span class="stat-label">Employees Worldwide</span></div>
  <div class="stat-divider"></div>
  <div class="stat"><span class="stat-num">#1</span><span class="stat-label">Energy Company in Africa</span></div>
  <div class="stat-divider"></div>
  <div class="stat"><span class="stat-num">22 000 km</span><span class="stat-label">Pipeline Network</span></div>
</div>

<script>
// Multi-file preview handling
function setupPreview(inputId, previewImgId, previewWrapId, zoneId) {
  const zone = document.getElementById(zoneId);
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewImgId);
  const prevWrap = document.getElementById(previewWrapId);

  function showPreview(file) {
    if (!file || !file.type.startsWith('image/')) return;
    const url = URL.createObjectURL(file);
    preview.src = url;
    prevWrap.style.display = 'block';
    zone.classList.add('has-preview');
  }

  input.addEventListener('change', () => { if (input.files[0]) showPreview(input.files[0]); });

  // remove button uses data-target attribute
  prevWrap.addEventListener('click', e => {
    if (e.target.matches('.remove-photo')) {
      input.value = '';
      preview.src = '#';
      prevWrap.style.display = 'none';
      zone.classList.remove('has-preview');
    }
  });

  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) { input.files = e.dataTransfer.files; showPreview(file); }
  });
}

setupPreview('national_id_card','previewIDImg','previewID','uploadZoneID');
setupPreview('self_photo','previewSelfImg','previewSelf','uploadZoneSelf');
setupPreview('birth_certificate','previewBirthImg','previewBirth','uploadZoneBirth');
</script>
</body>
</html>
