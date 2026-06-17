<?php
include('includes/dbconnect.php');
include('includes/db.php');
include('includes/auth.php');
include('includes/helpers.php');
requireLogin();

$db = DB::getInstance();
$formId = (int)($_GET['id'] ?? 0);

if (!$formId || !canAccessForm($formId)) {
    redirect(APP_URL . '/forms.php');
}

$form   = $db->fetch("SELECT * FROM forms WHERE id = ?", [$formId]);
$fields = $db->fetchAll("SELECT * FROM fields WHERE form_id = ? ORDER BY sort_order ASC", [$formId]);
$fieldTypes = getFormFieldTypes();

$pageTitle = 'Edit: ' . $form['name'] ;

// Handle AJAX save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');

    switch ($_POST['ajax_action']) {

        case 'save_form_settings':
            $data = [
                'name'            => sanitize($_POST['name'] ?? $form['name']),
                'description'     => sanitize($_POST['description'] ?? ''),
                'status'          => in_array($_POST['status'] ?? '', ['active','inactive','draft']) ? $_POST['status'] : $form['status'],
                'success_message' => sanitize($_POST['success_message'] ?? ''),
                'allow_multiple'  => isset($_POST['allow_multiple']) ? 1 : 0,
                'honeypot'        => isset($_POST['honeypot']) ? 1 : 0,
                'custom_css'      => $_POST['custom_css'] ?? '',
                'updated_at'      => date('Y-m-d H:i:s'),
            ];
            $db->update('forms', $data, 'id = ?', [$formId]);
            echo json_encode(['ok' => true, 'name' => $data['name']]);
            exit;

        case 'save_fields':
            $fieldsData = json_decode($_POST['fields'] ?? '[]', true);
            if (!is_array($fieldsData)) { echo json_encode(['ok'=>false,'msg'=>'Invalid data']); exit; }

            $db->beginTransaction();
            try {
                $db->delete('fields', 'form_id = ?', [$formId]);
                foreach ($fieldsData as $i => $f) {
                    $type = sanitize($f['type'] ?? 'text');
                    $db->insert('fields', [
                        'form_id'        => $formId,
                        'field_key'      => sanitize($f['field_key'] ?? 'field_' . ($i+1)),
                        'label'          => sanitize($f['label'] ?? 'Field ' . ($i+1)),
                        'type'           => $type,
                        'placeholder'    => sanitize($f['placeholder'] ?? ''),
                        'default_value'  => sanitize($f['default_value'] ?? ''),
                        'options'        => isset($f['options']) && is_array($f['options']) ? json_encode($f['options']) : ($f['options'] ?? null),
                        'required'       => isset($f['required']) && $f['required'] ? 1 : 0,
                        'css_class'      => sanitize($f['css_class'] ?? ''),
                        'container_class'=> sanitize($f['container_class'] ?? 'col-12'),
                        'sort_order'     => $i,
                        'help_text'      => sanitize($f['help_text'] ?? ''),
                        'min_length'     => ($f['min_length'] !== '' && $f['min_length'] !== null) ? (int)$f['min_length'] : null,
                        'max_length'     => ($f['max_length'] !== '' && $f['max_length'] !== null) ? (int)$f['max_length'] : null,
                        'rows'           => !empty($f['rows']) ? (int)$f['rows'] : 4,
                        'accept'         => sanitize($f['accept'] ?? ''),
                        'multiple_files' => isset($f['multiple_files']) && $f['multiple_files'] ? 1 : 0,
                    ]);
                }
                $db->commit();
                echo json_encode(['ok' => true]);
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
            }
            exit;

        case 'add_field':
            $type = sanitize($_POST['type'] ?? 'text');
            $label = sanitize($_POST['label'] ?? ucfirst($type) . ' Field');
            $maxOrder = $db->fetch("SELECT MAX(sort_order) m FROM fields WHERE form_id=?", [$formId])['m'] ?? -1;
            $key = 'field_' . strtolower(preg_replace('/[^a-z0-9]+/i','-',$label)) . '_' . rand(100,999);
            $fieldId = $db->insert('fields', [
                'form_id'     => $formId,
                'field_key'   => $key,
                'label'       => $label,
                'type'        => $type,
                'sort_order'  => $maxOrder + 1,
                'container_class' => 'col-12',
                'options'     => in_array($type, ['select','radio','checkbox']) ? json_encode(['Option 1','Option 2','Option 3']) : null,
            ]);
            echo json_encode(['ok' => true, 'field_id' => $fieldId, 'field_key' => $key]);
            exit;
    }
    echo json_encode(['ok' => false, 'msg' => 'Unknown action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0f1117;--surface:#181b23;--surface2:#1e2230;--surface3:#232738;
  --border:#2a2f3e;--border2:#343a4d;
  --accent:#4f6ef7;--accent2:#7c3aed;--accent-glow:rgba(79,110,247,.18);
  --green:#22c55e;--red:#ef4444;--yellow:#f59e0b;
  --text:#e8eaf0;--text2:#9ba3b8;--text3:#5c6480;
  --radius:10px;--radius-sm:6px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);font-size:14px}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:var(--surface)}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}

/* Builder layout */
.builder{display:flex;height:100vh;overflow:hidden}

/* LEFT: Field palette */
.palette{width:230px;min-width:230px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden}
.palette-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.palette-header h2{font-size:13px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.5px}
.palette-search{padding:10px 12px;border-bottom:1px solid var(--border)}
.palette-search input{width:100%;background:var(--surface2);border:1px solid var(--border2);border-radius:var(--radius-sm);padding:7px 10px;color:var(--text);font-family:inherit;font-size:12.5px;outline:none}
.palette-search input:focus{border-color:var(--accent)}
.palette-body{flex:1;overflow-y:auto;padding:8px 0}
.palette-group-label{padding:8px 14px 4px;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--text3)}
.palette-field{display:flex;align-items:center;gap:8px;padding:8px 14px;cursor:grab;color:var(--text2);font-size:13px;font-weight:500;transition:all .12s;user-select:none;border-radius:0}
.palette-field:hover{background:var(--surface2);color:var(--text)}
.palette-field:active{cursor:grabbing}
.palette-field-icon{width:28px;height:28px;border-radius:6px;background:var(--surface2);border:1px solid var(--border2);display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;transition:all .12s}
.palette-field:hover .palette-field-icon{background:var(--accent-glow);border-color:var(--accent);color:var(--accent)}

/* CENTER: Canvas */
.canvas-wrap{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--bg)}
.builder-topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 20px;height:54px;display:flex;align-items:center;gap:12px;flex-shrink:0}
.form-name-display{font-size:15px;font-weight:700;color:var(--text);cursor:pointer}
.form-name-display:hover{color:var(--accent)}
.builder-tabs{display:flex;gap:2px;background:var(--surface2);border-radius:8px;padding:3px;margin-left:auto}
.builder-tab{padding:6px 14px;font-size:12.5px;font-weight:600;color:var(--text3);cursor:pointer;border-radius:6px;transition:all .15s;user-select:none}
.builder-tab.active{background:var(--surface);color:var(--text);box-shadow:0 1px 4px rgba(0,0,0,.3)}
.save-btn{background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);padding:8px 18px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:6px}
.save-btn:hover{background:#3d5bf0;transform:translateY(-1px)}
.save-btn.saving{opacity:.7;pointer-events:none}

.canvas-scroll{flex:1;overflow-y:auto;padding:24px}
.canvas-inner{max-width:720px;margin:0 auto}

/* Tab panels */
.tab-panel{display:none}
.tab-panel.active{display:block}

/* Drop zone */
.drop-zone{min-height:400px;border:2px dashed var(--border2);border-radius:var(--radius);padding:16px;transition:all .2s;position:relative}
.drop-zone.drag-over{border-color:var(--accent);background:var(--accent-glow)}
.drop-zone.empty::before{content:'Drag fields here to build your form';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:14px;pointer-events:none}

/* Field cards in canvas */
.field-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:8px;cursor:default;transition:all .15s;position:relative;overflow:hidden}
.field-card:hover{border-color:var(--border2)}
.field-card.selected{border-color:var(--accent);box-shadow:0 0 0 2px var(--accent-glow)}
.field-card.dragging{opacity:.4;border-style:dashed}
.field-card-inner{padding:12px 14px;display:flex;align-items:flex-start;gap:12px}
.field-drag-handle{color:var(--text3);cursor:grab;padding:2px 0;font-size:12px;flex-shrink:0;margin-top:2px}
.field-drag-handle:active{cursor:grabbing}
.field-info{flex:1;min-width:0}
.field-type-badge{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text3);margin-bottom:3px;display:flex;align-items:center;gap:5px}
.field-label-text{font-weight:600;color:var(--text);font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.field-required-star{color:var(--red);font-size:13px}
.field-preview{margin-top:8px}
.field-preview input,.field-preview textarea,.field-preview select{
  width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);
  padding:7px 10px;color:var(--text2);font-family:inherit;font-size:12.5px;outline:none;pointer-events:none
}
.field-preview textarea{resize:none;min-height:60px}
.field-preview .preview-radio,.field-preview .preview-check{display:flex;gap:14px;flex-wrap:wrap}
.field-preview .radio-opt,.field-preview .check-opt{display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--text3)}
.field-preview .radio-dot{width:13px;height:13px;border:2px solid var(--border2);border-radius:50%}
.field-preview .check-box{width:13px;height:13px;border:2px solid var(--border2);border-radius:3px}
.field-heading-preview{font-size:18px;font-weight:700;color:var(--text);padding:6px 0}
.field-paragraph-preview{font-size:13px;color:var(--text2);line-height:1.7;padding:4px 0}
.field-divider-preview{border-top:1px solid var(--border2);margin:8px 0}
.field-spacer-preview{height:24px}
.field-actions{display:flex;flex-direction:column;gap:4px;flex-shrink:0}
.field-btn{width:28px;height:28px;border:1px solid var(--border2);border-radius:6px;background:var(--surface2);color:var(--text3);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:11px;transition:all .12s}
.field-btn:hover{color:var(--text);border-color:var(--border2);background:var(--border)}
.field-btn.del:hover{color:var(--red);border-color:var(--red)}

/* RIGHT: Properties panel */
.props-panel{width:300px;min-width:300px;background:var(--surface);border-left:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden}
.props-header{padding:14px 16px;border-bottom:1px solid var(--border)}
.props-header h3{font-size:13px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.5px}
.props-body{flex:1;overflow-y:auto;padding:14px}
.props-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:200px;color:var(--text3);text-align:center;gap:8px;font-size:13px}
.prop-group{margin-bottom:16px}
.prop-label{display:block;font-size:12px;font-weight:600;color:var(--text3);margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px}
.prop-input{width:100%;background:var(--surface2);border:1px solid var(--border2);border-radius:var(--radius-sm);padding:8px 10px;color:var(--text);font-family:inherit;font-size:13px;outline:none;transition:border-color .15s}
.prop-input:focus{border-color:var(--accent)}
.prop-input::placeholder{color:var(--text3)}
textarea.prop-input{resize:vertical;min-height:70px}
select.prop-input{cursor:pointer}
.prop-toggle{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)}
.prop-toggle:last-child{border-bottom:none}
.toggle-label{font-size:13px;color:var(--text2)}
.toggle-switch{position:relative;width:38px;height:20px;cursor:pointer}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;inset:0;background:var(--border2);border-radius:10px;transition:.2s}
.toggle-slider::before{content:'';position:absolute;width:14px;height:14px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s}
.toggle-switch input:checked+.toggle-slider{background:var(--accent)}
.toggle-switch input:checked+.toggle-slider::before{transform:translateX(18px)}
.prop-divider{border:none;border-top:1px solid var(--border);margin:12px 0}
.options-list{display:flex;flex-direction:column;gap:6px;margin-bottom:8px}
.option-row{display:flex;gap:6px;align-items:center}
.option-row input{flex:1;background:var(--surface2);border:1px solid var(--border2);border-radius:var(--radius-sm);padding:6px 8px;color:var(--text);font-family:inherit;font-size:12.5px;outline:none}
.option-row input:focus{border-color:var(--accent)}
.del-opt-btn{width:22px;height:22px;border:none;background:none;color:var(--text3);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:11px;border-radius:4px;transition:color .12s}
.del-opt-btn:hover{color:var(--red)}
.add-opt-btn{background:none;border:1px dashed var(--border2);border-radius:var(--radius-sm);color:var(--text3);font-family:inherit;font-size:12px;padding:5px 10px;cursor:pointer;width:100%;transition:all .15s}
.add-opt-btn:hover{border-color:var(--accent);color:var(--accent)}

/* Settings panel */
.settings-grid{display:flex;flex-direction:column;gap:16px}
.settings-section{background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius);padding:16px}
.settings-section h4{font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;display:flex;align-items:center;gap:7px}
.settings-section h4 i{color:var(--accent)}
.setting-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.setting-row:last-child{margin-bottom:0}
.setting-label{font-size:13px;color:var(--text2)}
.setting-help{font-size:11px;color:var(--text3);margin-top:2px}

/* Toast */
.toast{position:fixed;bottom:24px;right:24px;background:var(--surface2);border:1px solid var(--border2);border-radius:var(--radius);padding:12px 18px;font-size:13.5px;color:var(--text);display:flex;align-items:center;gap:10px;box-shadow:0 8px 32px rgba(0,0,0,.4);z-index:9999;transform:translateY(80px);opacity:0;transition:all .3s cubic-bezier(.34,1.56,.64,1)}
.toast.show{transform:translateY(0);opacity:1}
.toast.success{border-left:3px solid var(--green)}
.toast.error{border-left:3px solid var(--red)}

/* Link box */
.link-box{background:var(--surface2);border:1px solid var(--border2);border-radius:var(--radius-sm);padding:10px 12px;display:flex;align-items:center;gap:8px;margin-top:8px}
.link-box span{flex:1;font-size:12.5px;color:var(--text2);font-family:'JetBrains Mono',monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.copy-btn{background:var(--surface);border:1px solid var(--border2);border-radius:5px;color:var(--text3);cursor:pointer;padding:4px 8px;font-size:11px;font-family:inherit;transition:all .12s}
.copy-btn:hover{color:var(--accent);border-color:var(--accent)}

/* Back link */
.back-link{color:var(--text3);text-decoration:none;font-size:13px;display:flex;align-items:center;gap:6px;transition:color .15s;padding:4px 0}
.back-link:hover{color:var(--text)}

/* Drag placeholder */
.drag-placeholder{height:52px;border:2px dashed var(--accent);border-radius:var(--radius-sm);background:var(--accent-glow);margin-bottom:8px}
</style>
</head>
<body>
<div class="builder">

<!-- LEFT: Field Palette -->
<div class="palette" id="palette">
  <div class="palette-header">
    <a href="<?= APP_URL ?>/forms.php" class="back-link"><i class="fa-solid fa-chevron-left"></i> Forms</a>
  </div>
  <div class="palette-search">
    <input type="text" id="paletteSearch" placeholder="Search fields..." oninput="filterPalette(this.value)">
  </div>
  <div class="palette-body" id="paletteBody">
    <?php foreach ($fieldTypes as $group => $types): ?>
    <div class="palette-group" data-group="<?= e($group) ?>">
      <div class="palette-group-label"><?= e($group) ?></div>
      <?php foreach ($types as $type => $info): ?>
      <div class="palette-field" draggable="true" data-type="<?= e($type) ?>" data-label="<?= e($info['label']) ?>"
           ondragstart="paletteDragStart(event)" title="<?= e($info['label']) ?>">
        <div class="palette-field-icon"><i class="fa-solid <?= e($info['icon']) ?>"></i></div>
        <?= e($info['label']) ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- CENTER: Canvas -->
<div class="canvas-wrap">
  <div class="builder-topbar">
    <span class="form-name-display" id="formNameDisplay" onclick="selectSettings()"><?= e($form['name']) ?></span>
    <span style="color:var(--text3);font-size:12px;margin-left:2px" id="formStatusBadge">
      <span class="badge badge-<?= $form['status'] ?>" style="font-size:11px"><?= $form['status'] ?></span>
    </span>
    <div class="builder-tabs">
      <div class="builder-tab active" onclick="switchTab('build')">Build</div>
      <div class="builder-tab" onclick="switchTab('settings')">Settings</div>
      <div class="builder-tab" onclick="switchTab('preview')">Preview</div>
    </div>
    <button class="save-btn" onclick="saveAll()" id="saveBtn"><i class="fa-solid fa-floppy-disk"></i> Save</button>
    <a href="<?= APP_URL ?>/public/form.php?id=<?= $formId ?>" target="_blank" class="save-btn" style="background:var(--surface2);color:var(--text2);border:1px solid var(--border2);text-decoration:none"><i class="fa-solid fa-eye"></i> View</a>
    <a href="<?= APP_URL ?>/submissions.php?form_id=<?= $formId ?>" class="save-btn" style="background:var(--surface2);color:var(--text2);border:1px solid var(--border2);text-decoration:none"><i class="fa-solid fa-inbox"></i> Entries</a>
  </div>

  <div class="canvas-scroll">
    <div class="canvas-inner">

      <!-- Build Tab -->
      <div id="tab-build" class="tab-panel active">
        <div class="drop-zone" id="dropZone"
             ondragover="canvasDragOver(event)" ondrop="canvasDrop(event)"
             ondragleave="canvasDragLeave(event)">
        </div>
      </div>

      <!-- Settings Tab -->
      <div id="tab-settings" class="tab-panel">
        <div class="settings-grid">
          <div class="settings-section">
            <h4><i class="fa-solid fa-sliders"></i> Form Settings</h4>
            <div class="prop-group">
              <label class="prop-label">Form Name</label>
              <input type="text" class="prop-input" id="setting_name" value="<?= e($form['name']) ?>">
            </div>
            <div class="prop-group">
              <label class="prop-label">Description</label>
              <textarea class="prop-input" id="setting_description"><?= e($form['description'] ?? '') ?></textarea>
            </div>
            <div class="prop-group">
              <label class="prop-label">Status</label>
              <select class="prop-input" id="setting_status">
                <option value="draft" <?= $form['status']==='draft'?'selected':'' ?>>Draft</option>
                <option value="active" <?= $form['status']==='active'?'selected':'' ?>>Active</option>
                <option value="inactive" <?= $form['status']==='inactive'?'selected':'' ?>>Inactive</option>
              </select>
            </div>
          </div>
          <div class="settings-section">
            <h4><i class="fa-solid fa-check-circle"></i> Submission Settings</h4>
            <div class="prop-group">
              <label class="prop-label">Success Message</label>
              <textarea class="prop-input" id="setting_success_message" rows="3"><?= e($form['success_message'] ?? 'Thank you! Your submission has been received.') ?></textarea>
            </div>
            <div class="setting-row">
              <div>
                <div class="setting-label">Allow Multiple Submissions</div>
                <div class="setting-help">Same user can submit multiple times</div>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" id="setting_allow_multiple" <?= $form['allow_multiple'] ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="setting-row">
              <div>
                <div class="setting-label">Honeypot Spam Protection</div>
                <div class="setting-help">Hidden field to catch bots</div>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" id="setting_honeypot" <?= $form['honeypot'] ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>
          <div class="settings-section">
            <h4><i class="fa-solid fa-link"></i> Form URL</h4>
            <div class="setting-help" style="font-size:13px;color:var(--text2);margin-bottom:8px">Share this link for people to fill out your form:</div>
            <div class="link-box">
              <span id="formLinkText"><?= APP_URL ?>/public/form.php?id=<?= $formId ?></span>
              <button class="copy-btn" onclick="copyLink()"><i class="fa-solid fa-copy"></i> Copy</button>
            </div>
          </div>
          <div class="settings-section">
            <h4><i class="fa-solid fa-code"></i> Custom CSS</h4>
            <textarea class="prop-input" id="setting_custom_css" rows="6" style="font-family:'JetBrains Mono',monospace;font-size:12px" placeholder="/* Add custom CSS for your form */"><?= e($form['custom_css'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Preview Tab -->
      <div id="tab-preview" class="tab-panel">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
          <div style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:12px;color:var(--text3);display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-eye"></i> Live Preview
            <a href="<?= APP_URL ?>/public/form.php?id=<?= $formId ?>" target="_blank" style="margin-left:auto;color:var(--accent);font-size:12px;text-decoration:none">Open full page <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
          </div>
          <iframe id="previewFrame" src="<?= APP_URL ?>/public/form.php?id=<?= $formId ?>&preview=1" style="width:100%;height:600px;border:none;background:#fff"></iframe>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- RIGHT: Properties Panel -->
<div class="props-panel" id="propsPanel">
  <div class="props-header">
    <h3 id="propsPanelTitle">Field Properties</h3>
  </div>
  <div class="props-body" id="propsBody">
    <div class="props-empty">
      <i class="fa-solid fa-arrow-left" style="font-size:24px;opacity:.3"></i>
      <div>Select a field to edit its properties</div>
    </div>
  </div>
</div>

</div><!-- .builder -->

<!-- Toast notification -->
<div class="toast" id="toast"></div>

<script>
// ============================================================
//  State
// ============================================================
const FORM_ID = <?= $formId ?>;
const APP_URL = '<?= APP_URL ?>';
let fields = <?= json_encode(array_map(function($f) {
    return [
        'id'            => $f['id'],
        'field_key'     => $f['field_key'],
        'label'         => $f['label'],
        'type'          => $f['type'],
        'placeholder'   => $f['placeholder'] ?? '',
        'default_value' => $f['default_value'] ?? '',
        'options'       => $f['options'] ? json_decode($f['options'],true) : null,
        'required'      => (bool)$f['required'],
        'css_class'     => $f['css_class'] ?? '',
        'container_class'=> $f['container_class'] ?? 'col-12',
        'help_text'     => $f['help_text'] ?? '',
        'min_length'    => $f['min_length'],
        'max_length'    => $f['max_length'],
        'rows'          => $f['rows'] ?? 4,
        'accept'        => $f['accept'] ?? '',
        'multiple_files'=> (bool)$f['multiple_files'],
    ];
}, $fields)) ?>;

let selectedIdx = null;
let dragSrcIdx = null;      // canvas drag (reorder)
let paletteDragType = null; // palette drag
let dirty = false;

// ============================================================
//  Render canvas
// ============================================================
function renderCanvas() {
    const dz = document.getElementById('dropZone');
    dz.innerHTML = '';
    dz.classList.toggle('empty', fields.length === 0);

    fields.forEach((f, i) => {
        const card = document.createElement('div');
        card.className = 'field-card' + (selectedIdx === i ? ' selected' : '');
        card.draggable = true;
        card.dataset.idx = i;
        card.innerHTML = buildFieldCard(f, i);
        card.addEventListener('click', () => selectField(i));
        card.addEventListener('dragstart', e => cardDragStart(e, i));
        card.addEventListener('dragover', e => cardDragOver(e, i));
        card.addEventListener('dragend', cardDragEnd);
        dz.appendChild(card);
    });

    // Keyboard shortcut: delete selected with Delete key
}

function buildFieldCard(f, i) {
    const isLayout = ['heading','paragraph','divider','spacer'].includes(f.type);
    const typeInfo = getFieldTypeInfo(f.type);
    return `
    <div class="field-card-inner">
      <i class="fa-solid fa-grip-vertical field-drag-handle"></i>
      <div class="field-info">
        <div class="field-type-badge">
          <i class="fa-solid ${typeInfo.icon}" style="color:var(--accent)"></i>
          ${typeInfo.label}
        </div>
        <div class="field-label-text">${esc(f.label)}${f.required ? ' <span class="field-required-star">*</span>' : ''}</div>
        ${buildFieldPreview(f)}
      </div>
      <div class="field-actions">
        <button class="field-btn" onclick="event.stopPropagation(); duplicateField(${i})" title="Duplicate"><i class="fa-solid fa-copy"></i></button>
        <button class="field-btn del" onclick="event.stopPropagation(); deleteField(${i})" title="Delete"><i class="fa-solid fa-trash"></i></button>
      </div>
    </div>`;
}

function buildFieldPreview(f) {
    switch (f.type) {
        case 'text': case 'email': case 'tel': case 'url': case 'password':
            return `<div class="field-preview"><input type="${f.type}" placeholder="${esc(f.placeholder || f.label)}" tabindex="-1"></div>`;
        case 'number':
            return `<div class="field-preview"><input type="number" placeholder="${esc(f.placeholder || '0')}" tabindex="-1"></div>`;
        case 'textarea':
            return `<div class="field-preview"><textarea placeholder="${esc(f.placeholder || f.label)}" rows="2" tabindex="-1"></textarea></div>`;
        case 'select':
            const sopts = (f.options || []).map(o => `<option>${esc(o)}</option>`).join('');
            return `<div class="field-preview"><select tabindex="-1"><option value="">— Select —</option>${sopts}</select></div>`;
        case 'radio':
            const ropts = (f.options || []).slice(0,3).map(o => `<div class="radio-opt"><div class="radio-dot"></div>${esc(o)}</div>`).join('');
            return `<div class="field-preview"><div class="preview-radio">${ropts}</div></div>`;
        case 'checkbox':
            const copts = (f.options || []).slice(0,3).map(o => `<div class="check-opt"><div class="check-box"></div>${esc(o)}</div>`).join('');
            return `<div class="field-preview"><div class="preview-check">${copts}</div></div>`;
        case 'date': case 'time': case 'datetime-local':
            return `<div class="field-preview"><input type="${f.type}" tabindex="-1"></div>`;
        case 'file':
            return `<div class="field-preview"><input type="text" placeholder="Choose file..." tabindex="-1"></div>`;
        case 'signature':
            return `<div class="field-preview" style="background:var(--surface2);border:1px solid var(--border);border-radius:6px;height:50px;display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:12px"><i class="fa-solid fa-pen-nib" style="margin-right:6px"></i> Signature pad</div>`;
        case 'hidden':
            return `<div class="field-preview" style="font-size:11.5px;color:var(--text3);font-style:italic">Hidden field — not shown to users</div>`;
        case 'heading':
            return `<div class="field-heading-preview">${esc(f.label)}</div>`;
        case 'paragraph':
            return `<div class="field-paragraph-preview">${esc(f.default_value || 'Paragraph text...')}</div>`;
        case 'divider':
            return `<div class="field-divider-preview"></div>`;
        case 'spacer':
            return `<div class="field-spacer-preview"></div>`;
        default:
            return `<div class="field-preview"><input type="text" placeholder="${esc(f.label)}" tabindex="-1"></div>`;
    }
}

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ============================================================
//  Field selection + properties panel
// ============================================================
function selectField(idx) {
    selectedIdx = idx;
    renderCanvas();
    renderPropsPanel();
}

function selectSettings() {
    selectedIdx = null;
    renderCanvas();
    renderPropsPanel();
}

function renderPropsPanel() {
    if (selectedIdx === null) {
        document.getElementById('propsPanelTitle').textContent = 'Field Properties';
        document.getElementById('propsBody').innerHTML = `
          <div class="props-empty">
            <i class="fa-solid fa-arrow-left" style="font-size:24px;opacity:.3"></i>
            <div>Select a field to edit its properties</div>
          </div>`;
        return;
    }

    const f = fields[selectedIdx];
    const isLayout = ['heading','paragraph','divider','spacer'].includes(f.type);
    const isChoice = ['select','radio','checkbox'].includes(f.type);
    const isText   = ['text','email','tel','url','password','textarea'].includes(f.type);

    document.getElementById('propsPanelTitle').textContent = getFieldTypeInfo(f.type).label + ' Properties';

    let html = '';

    // Label
    if (!['divider','spacer'].includes(f.type)) {
        html += propInput('Label', 'label', f.label, 'text');
    }

    // Placeholder
    if (!isLayout && !isChoice) {
        html += propInput('Placeholder', 'placeholder', f.placeholder || '', 'text');
    }

    // Default value / content
    if (f.type === 'paragraph') {
        html += propTextarea('Content', 'default_value', f.default_value || '');
    } else if (!isLayout && !isChoice && f.type !== 'file' && f.type !== 'signature') {
        html += propInput('Default Value', 'default_value', f.default_value || '', 'text');
    }

    // Options for choice fields
    if (isChoice) {
        html += `<div class="prop-group">
          <label class="prop-label">Options</label>
          <div class="options-list" id="optionsList">
            ${(f.options||[]).map((o,oi) => optionRow(o, oi)).join('')}
          </div>
          <button class="add-opt-btn" onclick="addOption()"><i class="fa-solid fa-plus"></i> Add Option</button>
        </div>`;
    }

    // Text constraints
    if (isText) {
        html += `<div class="prop-group" style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          ${propInput('Min Length','min_length',f.min_length||'','number')}
          ${propInput('Max Length','max_length',f.max_length||'','number')}
        </div>`;
    }

    // Textarea rows
    if (f.type === 'textarea') {
        html += propInput('Rows', 'rows', f.rows || 4, 'number');
    }

    // File settings
    if (f.type === 'file') {
        html += propInput('Accept (e.g. image/*,.pdf)', 'accept', f.accept || '', 'text');
        html += `<div class="prop-toggle">
          <span class="toggle-label">Allow Multiple Files</span>
          <label class="toggle-switch">
            <input type="checkbox" id="prop_multiple_files" ${f.multiple_files ? 'checked' : ''} onchange="setPropBool('multiple_files',this.checked)">
            <span class="toggle-slider"></span>
          </label>
        </div>`;
    }

    // Required
    if (!isLayout) {
        html += `<hr class="prop-divider"><div class="prop-toggle">
          <span class="toggle-label">Required Field</span>
          <label class="toggle-switch">
            <input type="checkbox" id="prop_required" ${f.required ? 'checked' : ''} onchange="setPropBool('required',this.checked)">
            <span class="toggle-slider"></span>
          </label>
        </div>`;
    }

    // Help text
    if (!['divider','spacer'].includes(f.type)) {
        html += `<hr class="prop-divider">`;
        html += propTextarea('Help Text', 'help_text', f.help_text || '', 2);
    }

    // Advanced
    html += `<hr class="prop-divider">`;
    html += propInput('Field Key', 'field_key', f.field_key, 'text');
    html += propInput('CSS Class', 'css_class', f.css_class || '', 'text');

    html += `<div class="prop-group">
      <label class="prop-label">Column Width</label>
      <select class="prop-input" id="prop_container_class" onchange="setProp('container_class',this.value)">
        <option value="col-12" ${f.container_class==='col-12'?'selected':''}>Full Width (12/12)</option>
        <option value="col-6" ${f.container_class==='col-6'?'selected':''}>Half Width (6/12)</option>
        <option value="col-4" ${f.container_class==='col-4'?'selected':''}>One Third (4/12)</option>
        <option value="col-3" ${f.container_class==='col-3'?'selected':''}>Quarter (3/12)</option>
      </select>
    </div>`;

    document.getElementById('propsBody').innerHTML = html;

    // Attach live-update events
    document.querySelectorAll('#propsBody input[id^=prop_]:not([type=checkbox]), #propsBody textarea[id^=prop_], #propsBody select[id^=prop_]').forEach(el => {
        el.addEventListener('input', () => {
            const key = el.id.replace('prop_','');
            setProp(key, el.type === 'number' ? (el.value === '' ? null : +el.value) : el.value);
        });
    });
}

function propInput(label, key, value, type='text') {
    return `<div class="prop-group">
      <label class="prop-label">${label}</label>
      <input type="${type}" class="prop-input" id="prop_${key}" value="${esc(value)}" ${type==='number'?'min="0"':''}>
    </div>`;
}

function propTextarea(label, key, value, rows=3) {
    return `<div class="prop-group">
      <label class="prop-label">${label}</label>
      <textarea class="prop-input" id="prop_${key}" rows="${rows}">${esc(value)}</textarea>
    </div>`;
}

function optionRow(val, idx) {
    return `<div class="option-row" data-oi="${idx}">
      <input type="text" value="${esc(val)}" oninput="setOption(${idx},this.value)" placeholder="Option ${idx+1}">
      <button class="del-opt-btn" onclick="removeOption(${idx})" title="Remove"><i class="fa-solid fa-xmark"></i></button>
    </div>`;
}

function setProp(key, value) {
    if (selectedIdx === null) return;
    fields[selectedIdx][key] = value;
    dirty = true;
    // Re-render just the card
    const cards = document.querySelectorAll('.field-card');
    if (cards[selectedIdx]) {
        cards[selectedIdx].innerHTML = buildFieldCard(fields[selectedIdx], selectedIdx);
    }
}

function setPropBool(key, value) {
    setProp(key, value);
}

function setOption(oi, val) {
    if (selectedIdx === null) return;
    fields[selectedIdx].options[oi] = val;
    dirty = true;
    const cards = document.querySelectorAll('.field-card');
    if (cards[selectedIdx]) cards[selectedIdx].innerHTML = buildFieldCard(fields[selectedIdx], selectedIdx);
}

function addOption() {
    if (selectedIdx === null) return;
    if (!fields[selectedIdx].options) fields[selectedIdx].options = [];
    const n = fields[selectedIdx].options.length + 1;
    fields[selectedIdx].options.push('Option ' + n);
    dirty = true;
    renderPropsPanel();
    const cards = document.querySelectorAll('.field-card');
    if (cards[selectedIdx]) cards[selectedIdx].innerHTML = buildFieldCard(fields[selectedIdx], selectedIdx);
}

function removeOption(oi) {
    if (selectedIdx === null) return;
    fields[selectedIdx].options.splice(oi, 1);
    dirty = true;
    renderPropsPanel();
    const cards = document.querySelectorAll('.field-card');
    if (cards[selectedIdx]) cards[selectedIdx].innerHTML = buildFieldCard(fields[selectedIdx], selectedIdx);
}

// ============================================================
//  Palette drag → Canvas drop
// ============================================================
function paletteDragStart(e) {
    paletteDragType = e.currentTarget.dataset.type;
    e.dataTransfer.effectAllowed = 'copy';
}

function canvasDragOver(e) {
    if (paletteDragType || dragSrcIdx !== null) {
        e.preventDefault();
        e.dataTransfer.dropEffect = paletteDragType ? 'copy' : 'move';
        document.getElementById('dropZone').classList.add('drag-over');
    }
}

function canvasDragLeave(e) {
    if (!e.currentTarget.contains(e.relatedTarget)) {
        document.getElementById('dropZone').classList.remove('drag-over');
    }
}

function canvasDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').classList.remove('drag-over');
    if (paletteDragType) {
        addField(paletteDragType);
        paletteDragType = null;
    }
}

// ============================================================
//  Canvas drag (reorder)
// ============================================================
function cardDragStart(e, idx) {
    dragSrcIdx = idx;
    e.dataTransfer.effectAllowed = 'move';
    setTimeout(() => document.querySelectorAll('.field-card')[idx]?.classList.add('dragging'), 0);
}

function cardDragOver(e, idx) {
    if (dragSrcIdx === null || dragSrcIdx === idx) return;
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    // Move in array
    const moved = fields.splice(dragSrcIdx, 1)[0];
    fields.splice(idx, 0, moved);
    if (selectedIdx === dragSrcIdx) selectedIdx = idx;
    else if (selectedIdx !== null) {
        if (dragSrcIdx < idx && selectedIdx > dragSrcIdx && selectedIdx <= idx) selectedIdx--;
        else if (dragSrcIdx > idx && selectedIdx < dragSrcIdx && selectedIdx >= idx) selectedIdx++;
    }
    dragSrcIdx = idx;
    dirty = true;
    renderCanvas();
}

function cardDragEnd() {
    dragSrcIdx = null;
    document.querySelectorAll('.field-card.dragging').forEach(c => c.classList.remove('dragging'));
}

// ============================================================
//  Field CRUD
// ============================================================
function addField(type) {
    const typeInfo = getFieldTypeInfo(type);
    const key = type + '_' + Date.now().toString(36);
    const newField = {
        field_key: key,
        label: typeInfo.label + ' Field',
        type: type,
        placeholder: '',
        default_value: '',
        options: ['select','radio','checkbox'].includes(type) ? ['Option 1','Option 2','Option 3'] : null,
        required: false,
        css_class: '',
        container_class: 'col-12',
        help_text: '',
        min_length: null,
        max_length: null,
        rows: 4,
        accept: '',
        multiple_files: false,
    };
    fields.push(newField);
    selectedIdx = fields.length - 1;
    dirty = true;
    renderCanvas();
    renderPropsPanel();
    // Scroll to bottom
    document.getElementById('dropZone').lastElementChild?.scrollIntoView({behavior:'smooth',block:'nearest'});
}

function deleteField(idx) {
    if (!confirm('Delete this field?')) return;
    fields.splice(idx, 1);
    if (selectedIdx === idx) selectedIdx = null;
    else if (selectedIdx > idx) selectedIdx--;
    dirty = true;
    renderCanvas();
    renderPropsPanel();
}

function duplicateField(idx) {
    const copy = JSON.parse(JSON.stringify(fields[idx]));
    copy.field_key = copy.field_key + '_copy_' + Date.now().toString(36);
    fields.splice(idx + 1, 0, copy);
    selectedIdx = idx + 1;
    dirty = true;
    renderCanvas();
    renderPropsPanel();
}

// ============================================================
//  Save
// ============================================================
async function saveAll() {
    const btn = document.getElementById('saveBtn');
    btn.classList.add('saving');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    try {
        // Save fields
        const fd = new FormData();
        fd.append('ajax_action', 'save_fields');
        fd.append('fields', JSON.stringify(fields));
        const r = await fetch(window.location.href, {method:'POST', body: fd});
        const data = await r.json();
        if (!data.ok) throw new Error(data.msg || 'Save failed');

        // Save settings
        const fs = new FormData();
        fs.append('ajax_action', 'save_form_settings');
        fs.append('name', document.getElementById('setting_name')?.value || '');
        fs.append('description', document.getElementById('setting_description')?.value || '');
        fs.append('status', document.getElementById('setting_status')?.value || 'draft');
        fs.append('success_message', document.getElementById('setting_success_message')?.value || '');
        if (document.getElementById('setting_allow_multiple')?.checked) fs.append('allow_multiple','1');
        if (document.getElementById('setting_honeypot')?.checked) fs.append('honeypot','1');
        fs.append('custom_css', document.getElementById('setting_custom_css')?.value || '');
        const r2 = await fetch(window.location.href, {method:'POST', body: fs});
        const data2 = await r2.json();
        if (!data2.ok) throw new Error(data2.msg || 'Settings save failed');

        // Update UI
        if (data2.name) {
            document.getElementById('formNameDisplay').textContent = data2.name;
        }

        dirty = false;
        toast('Form saved successfully!', 'success');
    } catch(e) {
        toast('Error: ' + e.message, 'error');
    } finally {
        btn.classList.remove('saving');
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save';
    }
}

// Keyboard shortcut
document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 's') {
        e.preventDefault();
        saveAll();
    }
    if (e.key === 'Delete' || e.key === 'Backspace') {
        if (selectedIdx !== null && document.activeElement === document.body) {
            deleteField(selectedIdx);
        }
    }
    if (e.key === 'Escape') {
        selectedIdx = null;
        renderCanvas();
        renderPropsPanel();
    }
});

// ============================================================
//  Tab switching
// ============================================================
function switchTab(name) {
    document.querySelectorAll('.builder-tab').forEach((t,i) => {
        t.classList.toggle('active', ['build','settings','preview'][i] === name);
    });
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    if (name === 'preview') {
        document.getElementById('previewFrame').src = APP_URL + '/public/form.php?id=' + FORM_ID + '&preview=1&t=' + Date.now();
    }
}

// ============================================================
//  Palette filter
// ============================================================
function filterPalette(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.palette-field').forEach(el => {
        el.style.display = el.dataset.label.toLowerCase().includes(q) ? '' : 'none';
    });
    document.querySelectorAll('.palette-group').forEach(g => {
        const visible = [...g.querySelectorAll('.palette-field')].some(el => el.style.display !== 'none');
        g.style.display = visible ? '' : 'none';
    });
}

// ============================================================
//  Helpers
// ============================================================
function getFieldTypeInfo(type) {
    const all = {
        text:{label:'Text',icon:'fa-font'},
        email:{label:'Email',icon:'fa-envelope'},
        number:{label:'Number',icon:'fa-hashtag'},
        tel:{label:'Phone',icon:'fa-phone'},
        url:{label:'URL',icon:'fa-link'},
        password:{label:'Password',icon:'fa-lock'},
        textarea:{label:'Textarea',icon:'fa-align-left'},
        select:{label:'Dropdown',icon:'fa-chevron-down'},
        radio:{label:'Radio',icon:'fa-dot-circle'},
        checkbox:{label:'Checkbox',icon:'fa-check-square'},
        date:{label:'Date',icon:'fa-calendar'},
        time:{label:'Time',icon:'fa-clock'},
        'datetime-local':{label:'Date & Time',icon:'fa-calendar-alt'},
        file:{label:'File Upload',icon:'fa-upload'},
        signature:{label:'Signature',icon:'fa-pen-nib'},
        hidden:{label:'Hidden',icon:'fa-eye-slash'},
        heading:{label:'Heading',icon:'fa-heading'},
        paragraph:{label:'Paragraph',icon:'fa-paragraph'},
        divider:{label:'Divider',icon:'fa-minus'},
        spacer:{label:'Spacer',icon:'fa-arrows-alt-v'},
    };
    return all[type] || {label:type,icon:'fa-question'};
}

function toast(msg, type='success') {
    const t = document.getElementById('toast');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fa-solid fa-${type==='success'?'check-circle':'circle-exclamation'}"></i> ${msg}`;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}

function copyLink() {
    const url = document.getElementById('formLinkText').textContent;
    navigator.clipboard.writeText(url).then(() => toast('Link copied to clipboard!'));
}

// Warn on unload if dirty
window.addEventListener('beforeunload', e => {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
});

// ============================================================
//  Init
// ============================================================
renderCanvas();
<?php if (isset($_GET['new'])): ?>
toast('Form created! Now add fields to build your form.', 'success');
<?php endif; ?>
</script>
</body>
</html>
