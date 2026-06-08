<?php include('includes/dbconnect.php'); ?>
<?php
session_start();
if(@$_SESSION['user_type']!='' && @$_SESSION['user_type']==1){
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$assessment = null;
$questions  = [];
if($assessment_id > 0){
    $res = mysqli_query($connection, "SELECT * FROM assessment WHERE assessment_id = $assessment_id LIMIT 1");
    if($res && mysqli_num_rows($res) > 0) $assessment = mysqli_fetch_assoc($res);
    $qr = mysqli_query($connection, "SELECT * FROM assessment_questions WHERE assessment_id = $assessment_id ORDER BY question_id ASC");
    if($qr) while($row = mysqli_fetch_assoc($qr)) $questions[] = $row;
}
if(!$assessment){ header("Location: assessment_new.php"); exit; }
$totalQuestionsMarks = array_sum(array_column($questions,'marks'));
$remaining = intval($assessment['marks']) - $totalQuestionsMarks;

// All type definitions
$allTypes = [
    // Quiz types
    1  => ['Single Choice',   'ti-circle-check',   '#4f6ef7', 'quiz'],
    2  => ['True / False',    'ti-toggle-left',    '#8e44ad', 'quiz'],
    3  => ['Multiple Choice', 'ti-checks',         '#2980b9', 'quiz'],
    4  => ['Text Answer',     'ti-forms',          '#d35400', 'quiz'],
    5  => ['Image-based',     'ti-photo',          '#c0392b', 'quiz'],
    // Basic fields
    6  => ['Text',            'ti-letter-t',       '#0891b2', 'basic'],
    7  => ['Number',          'ti-hash',           '#0369a1', 'basic'],
    8  => ['URL',             'ti-link',           '#0284c7', 'basic'],
    9  => ['Textarea',        'ti-align-left',     '#0e7490', 'basic'],
    // Choice fields
    10 => ['Dropdown',        'ti-chevron-down',   '#7c3aed', 'choice'],
    11 => ['Radio',           'ti-circle-dot',     '#6d28d9', 'choice'],
    12 => ['Checkbox',        'ti-checkbox',       '#5b21b6', 'choice'],
    // Date & time
    13 => ['Date',            'ti-calendar',       '#b45309', 'datetime'],
    14 => ['Time',            'ti-clock',          '#92400e', 'datetime'],
    15 => ['Date & Time',     'ti-calendar-time',  '#78350f', 'datetime'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Add Question – <?php echo htmlspecialchars($assessment['assessment_name']); ?></title>
<link rel="shortcut icon" href="assets/images/favicon.ico">
<link rel="stylesheet" href="assets/css/icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.30.0/dist/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0f1117;--surface:#181b23;--surface2:#1e2230;--surface3:#252b3b;
  --border:#2a2f3e;--border2:#343a4d;
  --accent:#4f6ef7;--text:#e8eaf0;--text2:#9ba3b8;--text3:#5c6480;
  --green:#22c55e;--red:#ef4444;--yellow:#f59e0b;
  --radius:10px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden;font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);font-size:14px}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:var(--surface)}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}

/* ── Top bar ── */
.topbar{height:52px;background:var(--surface);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 18px;gap:12px;position:relative;z-index:10;}
.topbar-back{display:inline-flex;align-items:center;gap:6px;color:var(--text2);text-decoration:none;font-size:.82rem;font-weight:500;padding:5px 10px;border:1px solid var(--border2);border-radius:7px;transition:all .15s;}
.topbar-back:hover{background:var(--surface2);color:var(--text)}
.topbar-name{font-weight:700;font-size:.95rem;color:var(--text);margin-right:auto}
.topbar-badge{font-size:.7rem;font-weight:600;padding:2px 9px;border-radius:20px;background:var(--surface3);color:var(--text2);border:1px solid var(--border2);margin-left:6px;}
.topbar-stat{display:flex;align-items:center;gap:5px;font-size:.78rem;color:var(--text2);padding:5px 10px;border:1px solid var(--border2);border-radius:7px;background:var(--surface2)}
.topbar-stat b{color:var(--text);font-weight:700}

/* ── Layout ── */
.builder{display:flex;height:calc(100vh - 52px);overflow:hidden}

/* ── Palette ── */
.palette{width:220px;min-width:220px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;}
.palette-search{padding:10px 12px;border-bottom:1px solid var(--border);}
.palette-search input{width:100%;background:var(--surface2);border:1px solid var(--border2);border-radius:6px;padding:7px 10px;color:var(--text);font-family:inherit;font-size:12.5px;outline:none;}
.palette-search input:focus{border-color:var(--accent)}
.palette-search input::placeholder{color:var(--text3)}
.palette-body{flex:1;overflow-y:auto;padding:6px 0 16px}
.palette-group{padding:10px 14px 4px;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text3)}
.palette-item{display:flex;align-items:center;gap:9px;padding:8px 14px;cursor:grab;color:var(--text2);font-size:.82rem;font-weight:500;transition:all .12s;user-select:none;}
.palette-item:hover{background:var(--surface2);color:var(--text)}
.palette-item:active{cursor:grabbing}
.palette-item.dragging{opacity:.35}
.p-icon{width:28px;height:28px;border-radius:6px;border:1px solid var(--border2);background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;transition:all .12s;}
.palette-item:hover .p-icon{border-color:var(--accent);background:rgba(79,110,247,.1)}

/* ── Canvas ── */
.canvas-wrap{flex:1;overflow-y:auto;background:var(--bg);padding:28px 36px}
.canvas-inner{max-width:700px;margin:0 auto;display:flex;flex-direction:column;gap:10px;min-height:100%}
.drop-zone{border:2px dashed var(--border2);border-radius:var(--radius);padding:52px 20px;text-align:center;color:var(--text3);font-size:.875rem;transition:all .2s;margin-top:4px;}
.drop-zone.drag-over{border-color:var(--accent);background:rgba(79,110,247,.06);color:var(--accent)}
.drop-zone i{font-size:2rem;display:block;margin-bottom:10px;opacity:.35}

/* Question cards */
.q-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;cursor:pointer;transition:border-color .15s,box-shadow .15s;}
.q-card:hover{border-color:var(--border2);box-shadow:0 2px 12px rgba(0,0,0,.2)}
.q-card.active{border-color:var(--accent);box-shadow:0 0 0 2px rgba(79,110,247,.15)}
.q-card.pending{border-style:dashed;border-color:var(--accent);cursor:default}
.q-card-head{display:flex;align-items:center;gap:9px;padding:11px 14px;border-bottom:1px solid var(--border);background:var(--surface2);}
.q-num{width:22px;height:22px;border-radius:5px;background:var(--surface3);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:var(--text2);flex-shrink:0;}
.q-badge{font-size:.68rem;font-weight:600;padding:2px 8px;border-radius:20px;color:#fff;white-space:nowrap;}
.q-snippet{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text2);font-size:.8rem;margin-left:2px}
.q-actions{display:flex;gap:5px;margin-left:auto;flex-shrink:0}
.q-btn{width:28px;height:28px;border-radius:5px;border:1px solid var(--border2);background:var(--surface3);color:var(--text2);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:.82rem;transition:all .12s;}
.q-btn:hover{background:var(--surface2);color:var(--text)}
.q-btn.del{border-color:#7f1d1d;background:#7f1d1d33;color:#fca5a5}
.q-btn.del:hover{background:#c0392b;border-color:#c0392b;color:#fff}

/* ── Properties panel ── */
.props-panel{width:290px;min-width:290px;background:var(--surface);border-left:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;}
.props-header{padding:13px 16px;border-bottom:1px solid var(--border);font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text3);display:flex;align-items:center;justify-content:space-between;}
.props-type-chip{font-size:.7rem;font-weight:600;padding:2px 9px;border-radius:20px;color:#fff;text-transform:none;letter-spacing:0}
.props-body{flex:1;overflow-y:auto}
.props-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--text3);gap:10px;padding:20px;text-align:center;}
.props-empty i{font-size:2.2rem;opacity:.25}
.props-empty p{font-size:.82rem;line-height:1.6}

/* Form elements inside props */
.pf-sec{padding:13px 15px;border-bottom:1px solid var(--border)}
.pf-lbl{font-size:.68rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px;display:flex;align-items:center;justify-content:space-between}
.pf-inp,.pf-ta,.pf-sel{width:100%;background:var(--surface2);border:1px solid var(--border2);color:var(--text);border-radius:7px;padding:8px 11px;font-family:inherit;font-size:.84rem;outline:none;transition:border-color .15s;}
.pf-inp:focus,.pf-ta:focus,.pf-sel:focus{border-color:var(--accent)}
.pf-inp::placeholder,.pf-ta::placeholder{color:var(--text3)}
.pf-ta{resize:vertical;min-height:80px}
.pf-sel{appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235c6480' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;padding-right:28px}
.pf-row2{display:grid;grid-template-columns:1fr 1fr;gap:8px}

/* Options rows */
.opt-row{display:flex;align-items:center;gap:7px;margin-bottom:7px}
.opt-row input[type=radio],.opt-row input[type=checkbox]{accent-color:var(--accent);transform:scale(1.2);flex-shrink:0;cursor:pointer;}
.opt-row .pf-inp{flex:1;padding:6px 10px}
.opt-row .pf-inp.correct-hi{border-color:var(--green)!important;background:rgba(34,197,94,.07)!important}
.opt-del{width:24px;height:24px;border:none;background:transparent;color:var(--text3);cursor:pointer;border-radius:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.8rem}
.opt-del:hover{background:#c0392b;color:#fff}
.btn-add-opt{background:transparent;border:1px dashed var(--border2);color:var(--text3);border-radius:7px;padding:6px 12px;font-family:inherit;font-size:.78rem;cursor:pointer;width:100%;transition:all .15s;margin-top:2px}
.btn-add-opt:hover{border-color:var(--accent);color:var(--accent)}

/* True/False */
.tf-row{display:flex;gap:8px}
.tf-opt{flex:1;display:flex;align-items:center;justify-content:center;gap:7px;padding:10px;border:1px solid var(--border2);border-radius:8px;cursor:pointer;background:var(--surface2);color:var(--text2);font-weight:600;font-size:.84rem;transition:all .15s;}
.tf-opt input{accent-color:var(--accent);cursor:pointer}
.tf-opt.sel{border-color:var(--green);background:rgba(34,197,94,.1);color:var(--green)}

/* Marks */
.marks-row{display:flex;align-items:center;gap:8px}
.marks-row .pf-inp{width:85px}
.marks-hint{font-size:.72rem;color:var(--text3)}
.marks-hint.warn{color:var(--yellow)}
.marks-hint.err{color:var(--red)}

/* Save/cancel */
.btn-save{width:100%;background:var(--accent);color:#fff;border:none;border-radius:8px;padding:10px;font-family:inherit;font-size:.875rem;font-weight:700;cursor:pointer;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:7px}
.btn-save:hover{background:#3d5bf0}
.btn-save:disabled{opacity:.5;cursor:not-allowed}
.btn-cancel{width:100%;background:transparent;color:var(--text2);border:1px solid var(--border2);border-radius:8px;padding:9px;font-family:inherit;font-size:.84rem;cursor:pointer;transition:all .15s;margin-top:6px}
.btn-cancel:hover{background:var(--surface2);color:var(--text)}

/* Image upload */
.img-box{border:2px dashed var(--border2);border-radius:8px;padding:18px;text-align:center;color:var(--text3);font-size:.78rem;cursor:pointer;transition:all .15s}
.img-box:hover{border-color:var(--accent);color:var(--accent)}
.img-prev{max-width:100%;border-radius:7px;margin-top:8px;border:1px solid var(--border2)}

/* Toast */
.toast-wrap{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:7px}
.toast{background:var(--surface);border:1px solid var(--border);border-radius:9px;padding:11px 16px;color:var(--text);font-size:.84rem;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.3);display:flex;align-items:center;gap:9px;min-width:230px;animation:toastIn .22s ease}
.toast.success{border-left:3px solid var(--green)}
.toast.error{border-left:3px solid var(--red)}
@keyframes toastIn{from{transform:translateX(30px);opacity:0}to{transform:none;opacity:1}}
@keyframes spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<!-- Top bar -->
<div class="topbar">
  <a href="assessment_new.php" class="topbar-back"><i class="ti ti-arrow-left"></i> Assessments</a>
  <span class="topbar-name">
    <?php echo htmlspecialchars($assessment['assessment_name']); ?>
    <span class="topbar-badge"><?php echo htmlspecialchars($assessment['assessment_unique_id']); ?></span>
  </span>
  <div class="topbar-stat"><i class="ti ti-star" style="color:var(--yellow)"></i> Marks: <b><?php echo intval($assessment['marks']); ?></b></div>
  <div class="topbar-stat"><i class="ti ti-list-numbers" style="color:var(--accent)"></i> Questions: <b id="qCount"><?php echo count($questions); ?></b></div>
  <div class="topbar-stat"><i class="ti ti-adjustments" style="color:var(--green)"></i> Remaining: <b id="remainTop"><?php echo $remaining; ?></b></div>
</div>

<div class="builder">

  <!-- ── Left Palette ── -->
  <div class="palette">
    <div class="palette-search">
      <input type="text" id="paletteSearch" placeholder="Search fields…">
    </div>
    <div class="palette-body" id="paletteBody">

      <div class="palette-group">Question Types</div>
      <?php foreach($allTypes as $tid => $t): if($t[3]!=='quiz') continue; ?>
      <div class="palette-item" draggable="true" data-type="<?php echo $tid; ?>">
        <div class="p-icon"><i class="ti <?php echo $t[1]; ?>" style="color:<?php echo $t[2]; ?>"></i></div>
        <?php echo $t[0]; ?>
      </div>
      <?php endforeach; ?>

      <div class="palette-group">Basic Fields</div>
      <?php foreach($allTypes as $tid => $t): if($t[3]!=='basic') continue; ?>
      <div class="palette-item" draggable="true" data-type="<?php echo $tid; ?>">
        <div class="p-icon"><i class="ti <?php echo $t[1]; ?>" style="color:<?php echo $t[2]; ?>"></i></div>
        <?php echo $t[0]; ?>
      </div>
      <?php endforeach; ?>

      <div class="palette-group">Choice Fields</div>
      <?php foreach($allTypes as $tid => $t): if($t[3]!=='choice') continue; ?>
      <div class="palette-item" draggable="true" data-type="<?php echo $tid; ?>">
        <div class="p-icon"><i class="ti <?php echo $t[1]; ?>" style="color:<?php echo $t[2]; ?>"></i></div>
        <?php echo $t[0]; ?>
      </div>
      <?php endforeach; ?>

      <div class="palette-group">Date &amp; Time</div>
      <?php foreach($allTypes as $tid => $t): if($t[3]!=='datetime') continue; ?>
      <div class="palette-item" draggable="true" data-type="<?php echo $tid; ?>">
        <div class="p-icon"><i class="ti <?php echo $t[1]; ?>" style="color:<?php echo $t[2]; ?>"></i></div>
        <?php echo $t[0]; ?>
      </div>
      <?php endforeach; ?>

    </div>
  </div>

  <!-- ── Canvas ── -->
  <div class="canvas-wrap" id="canvasWrap">
    <div class="canvas-inner" id="canvasInner">

      <?php foreach($questions as $qi => $q):
        $qt  = intval($q['question_type']??1);
        $tdef = $allTypes[$qt] ?? ['Unknown','ti-help','#64748b','quiz'];
      ?>
      <div class="q-card" id="qcard<?php echo $q['question_id']; ?>"
           onclick="selectCard(<?php echo $q['question_id']; ?>)"
           data-qid="<?php echo $q['question_id']; ?>">
        <div class="q-card-head">
          <div class="q-num"><?php echo $qi+1; ?></div>
          <span class="q-badge" style="background:<?php echo $tdef[2]; ?>"><?php echo $tdef[0]; ?></span>
          <span class="q-snippet"><?php echo htmlspecialchars(mb_strimwidth($q['question_text'],0,60,'…')); ?></span>
          <div class="q-actions" onclick="event.stopPropagation()">
            <button class="q-btn" onclick="selectCard(<?php echo $q['question_id']; ?>)" title="Edit"><i class="ti ti-pencil"></i></button>
            <button class="q-btn del" onclick="deleteQ(<?php echo $q['question_id']; ?>)" title="Delete"><i class="ti ti-trash"></i></button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="drop-zone" id="dropZone">
        <i class="ti ti-drag-drop"></i>
        Drag a field type here to add a question
      </div>
    </div>
  </div>

  <!-- ── Properties Panel ── -->
  <div class="props-panel">
    <div class="props-header">
      FIELD PROPERTIES
      <span id="propsChip"></span>
    </div>
    <div class="props-body" id="propsBody">
      <div class="props-empty" id="propsEmpty">
        <i class="ti ti-arrow-left"></i>
        <p>Drag a field type from the left panel, or click an existing question to edit its properties.</p>
      </div>
    </div>
  </div>

</div>
<div class="toast-wrap" id="toastWrap"></div>

<script>
var assessmentId         = <?php echo $assessment_id; ?>;
var assessmentTotalMarks = <?php echo intval($assessment['marks']??0); ?>;
var currentTotalMarks    = <?php echo intval($totalQuestionsMarks); ?>;
var questionCount        = <?php echo count($questions); ?>;
var activeCardId         = null;
var pendingType          = null;
var optionCounter        = 0; // for dynamic options

var allTypes = <?php
  $out = [];
  foreach($allTypes as $tid => $t) $out[$tid] = ['name'=>$t[0],'icon'=>$t[1],'color'=>$t[2],'group'=>$t[3]];
  echo json_encode($out);
?>;

var existingQuestions = <?php
  $out = [];
  foreach($questions as $q){
    $out[] = [
      'question_id'           => intval($q['question_id']),
      'question_type'         => intval($q['question_type']??1),
      'question_text'         => $q['question_text'],
      'option_1'              => $q['option_1']??'',
      'option_2'              => $q['option_2']??'',
      'option_3'              => $q['option_3']??'',
      'option_4'              => $q['option_4']??'',
      'correct_option'        => $q['correct_option']??'',
      'correct_options_multi' => $q['correct_options_multi']??'',
      'marks'                 => intval($q['marks']),
    ];
  }
  echo json_encode($out);
?>;

// ── Helpers ──────────────────────────────────────
function esc(s){ var d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }
function val(id){ var el=document.getElementById(id); return el?el.value:''; }

function showToast(msg, type='success'){
  var w=document.getElementById('toastWrap'), d=document.createElement('div');
  d.className='toast '+type;
  d.innerHTML='<i class="ti '+(type==='success'?'ti-check':'ti-x')+'" style="color:'+(type==='success'?'#22c55e':'#ef4444')+'"></i>'+msg;
  w.appendChild(d); setTimeout(()=>d.remove(),3200);
}

function updateStats(){
  document.getElementById('qCount').textContent   = questionCount;
  document.getElementById('remainTop').textContent = assessmentTotalMarks - currentTotalMarks;
}

// ── Palette search ────────────────────────────────
document.getElementById('paletteSearch').addEventListener('input', function(){
  var q=this.value.toLowerCase();
  document.querySelectorAll('.palette-item').forEach(function(el){
    el.style.display = el.textContent.trim().toLowerCase().includes(q)?'':'none';
  });
});

// ── Drag from palette ─────────────────────────────
document.querySelectorAll('.palette-item').forEach(function(el){
  el.addEventListener('dragstart',function(e){ e.dataTransfer.setData('qtype',el.dataset.type); el.classList.add('dragging'); });
  el.addEventListener('dragend',function(){ el.classList.remove('dragging'); });
});

// ── Drop zone ─────────────────────────────────────
var dz=document.getElementById('dropZone');
dz.addEventListener('dragover',function(e){ e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave',function(){ dz.classList.remove('drag-over'); });
dz.addEventListener('drop',function(e){
  e.preventDefault(); dz.classList.remove('drag-over');
  var type=parseInt(e.dataTransfer.getData('qtype'));
  if(type) openNew(type);
});

// ── Open new question ─────────────────────────────
function openNew(type){
  var old=document.getElementById('pendingCard'); if(old) old.remove();
  document.querySelectorAll('.q-card').forEach(c=>c.classList.remove('active'));
  var t=allTypes[type]||{name:'Field',color:'#4f6ef7'};
  var card=document.createElement('div');
  card.className='q-card pending active'; card.id='pendingCard';
  card.innerHTML='<div class="q-card-head"><div class="q-num">+</div><span class="q-badge" style="background:'+t.color+'">'+t.name+'</span><span class="q-snippet" style="color:var(--text3)">New — fill properties →</span></div>';
  dz.parentNode.insertBefore(card,dz);
  activeCardId='new'; pendingType=type;
  buildForm(type,null);
  document.getElementById('canvasWrap').scrollTop=card.offsetTop-80;
}

// ── Select existing card ──────────────────────────
function selectCard(qid){
  document.querySelectorAll('.q-card').forEach(c=>c.classList.remove('active'));
  var pend=document.getElementById('pendingCard'); if(pend) pend.remove();
  var card=document.getElementById('qcard'+qid); if(card) card.classList.add('active');
  activeCardId=qid; pendingType=null;
  var q=existingQuestions.find(x=>x.question_id===qid);
  if(q) buildForm(q.question_type,q);
}

// ── Build form ────────────────────────────────────
function buildForm(type, qdata){
  var isEdit=qdata!==null;
  var t=allTypes[type]||{name:'Field',color:'#4f6ef7'};
  var origMarks=isEdit?(qdata.marks||0):0;
  var available=assessmentTotalMarks-currentTotalMarks+origMarks;

  // Header chip
  document.getElementById('propsChip').innerHTML='<span class="props-type-chip" style="background:'+t.color+'">'+t.name+'</span>';

  var h='';

  // ── Question / Label text ──
  h+=`<div class="pf-sec">
    <div class="pf-lbl">Label / Question *</div>
    <textarea class="pf-ta" id="pf_qtext" rows="3" placeholder="Enter question or label…">${isEdit?esc(qdata.question_text):''}</textarea>
  </div>`;

  // ── Type-specific sections ──

  // QUIZ TYPES
  if(type===1){ // Single Choice
    h+=optionsSection(type,qdata,isEdit,'radio','pf_correct','Select correct answer');
  } else if(type===2){ // True/False
    var tv=isEdit?String(qdata.correct_option):'';
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Correct Answer</div>
      <div class="tf-row">
        <label class="tf-opt ${tv==='True'?'sel':''}" id="tfT"><input type="radio" name="pf_tf" value="True" ${tv==='True'?'checked':''} onchange="tfSel()"><i class="ti ti-circle-check" style="color:var(--green)"></i>True</label>
        <label class="tf-opt ${tv==='False'?'sel':''}" id="tfF"><input type="radio" name="pf_tf" value="False" ${tv==='False'?'checked':''} onchange="tfSel()"><i class="ti ti-circle-x" style="color:var(--red)"></i>False</label>
      </div>
    </div>`;
  } else if(type===3){ // Multiple Choice
    h+=optionsSection(type,qdata,isEdit,'checkbox','pf_multi[]','Check all correct answers');
  } else if(type===4){ // Text Answer
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Expected Answer</div>
      <input type="text" class="pf-inp" id="pf_textans" placeholder="Exact expected answer" value="${isEdit?esc(qdata.correct_options_multi):''}">
    </div>`;
  } else if(type===5){ // Image-based
    var ei=isEdit?(qdata.correct_options_multi||''):'';
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Question Image *</div>
      <div class="img-box" onclick="document.getElementById('pf_imgfile').click()"><i class="ti ti-upload" style="font-size:1.3rem;display:block;margin-bottom:4px"></i>Click to upload (JPG/PNG/GIF/WEBP, max 2MB)</div>
      <input type="file" id="pf_imgfile" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none" onchange="prevImg(this)">
      <input type="hidden" id="pf_existimg" value="${esc(ei)}">
      <div id="pf_imgprev">${ei?'<img src="uploads/question_images/'+esc(ei)+'" class="img-prev">':''}</div>
    </div>`;
    h+=optionsSection(type,qdata,isEdit,'radio','pf_img_correct','Select correct option');

  // BASIC FIELDS
  } else if(type===6){ // Text
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Placeholder</div>
      <input type="text" class="pf-inp" id="pf_placeholder" placeholder="e.g. Enter your answer…" value="${isEdit?esc(qdata.option_1):''}">
    </div>
    <div class="pf-sec">
      <div class="pf-lbl">Expected Answer <small style="color:var(--text3);font-weight:400;text-transform:none">(optional)</small></div>
      <input type="text" class="pf-inp" id="pf_textans" placeholder="Leave blank if open-ended" value="${isEdit?esc(qdata.correct_options_multi):''}">
    </div>`;
  } else if(type===7){ // Number
    var mn=isEdit?(qdata.option_1||''):'', mx=isEdit?(qdata.option_2||''):'';
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Min / Max</div>
      <div class="pf-row2">
        <input type="number" class="pf-inp" id="pf_nummin" placeholder="Min" value="${esc(mn)}">
        <input type="number" class="pf-inp" id="pf_nummax" placeholder="Max" value="${esc(mx)}">
      </div>
    </div>
    <div class="pf-sec">
      <div class="pf-lbl">Expected Answer <small style="color:var(--text3);font-weight:400;text-transform:none">(optional)</small></div>
      <input type="number" class="pf-inp" id="pf_textans" placeholder="Correct number value" value="${isEdit?esc(qdata.correct_options_multi):''}">
    </div>`;
  } else if(type===8){ // URL
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Placeholder</div>
      <input type="text" class="pf-inp" id="pf_placeholder" placeholder="e.g. https://example.com" value="${isEdit?esc(qdata.option_1):''}">
    </div>
    <div class="pf-sec">
      <div class="pf-lbl">Expected URL <small style="color:var(--text3);font-weight:400;text-transform:none">(optional)</small></div>
      <input type="url" class="pf-inp" id="pf_textans" placeholder="https://…" value="${isEdit?esc(qdata.correct_options_multi):''}">
    </div>`;
  } else if(type===9){ // Textarea
    var rows=isEdit?(qdata.option_1||'4'):'4';
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Rows</div>
      <input type="number" class="pf-inp" id="pf_tarows" min="2" max="20" value="${esc(rows)}" placeholder="4">
    </div>
    <div class="pf-sec">
      <div class="pf-lbl">Placeholder</div>
      <input type="text" class="pf-inp" id="pf_placeholder" placeholder="e.g. Write your answer…" value="${isEdit?esc(qdata.option_2):''}">
    </div>
    <div class="pf-sec">
      <div class="pf-lbl">Model Answer <small style="color:var(--text3);font-weight:400;text-transform:none">(optional)</small></div>
      <textarea class="pf-ta" id="pf_textans" rows="3" placeholder="Reference answer…">${isEdit?esc(qdata.correct_options_multi):''}</textarea>
    </div>`;

  // CHOICE FIELDS
  } else if(type===10){ // Dropdown
    h+=dynamicOptionsSection(qdata,isEdit,'select','pf_dd_correct');
  } else if(type===11){ // Radio
    h+=dynamicOptionsSection(qdata,isEdit,'radio','pf_radio_correct');
  } else if(type===12){ // Checkbox
    h+=dynamicOptionsSection(qdata,isEdit,'checkbox','pf_chk_correct');

  // DATE & TIME
  } else if(type===13){ // Date
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Correct Date <small style="color:var(--text3);font-weight:400;text-transform:none">(optional)</small></div>
      <input type="date" class="pf-inp" id="pf_textans" value="${isEdit?esc(qdata.correct_options_multi):''}">
    </div>`;
  } else if(type===14){ // Time
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Correct Time <small style="color:var(--text3);font-weight:400;text-transform:none">(optional)</small></div>
      <input type="time" class="pf-inp" id="pf_textans" value="${isEdit?esc(qdata.correct_options_multi):''}">
    </div>`;
  } else if(type===15){ // Date & Time
    h+=`<div class="pf-sec">
      <div class="pf-lbl">Correct Date &amp; Time <small style="color:var(--text3);font-weight:400;text-transform:none">(optional)</small></div>
      <input type="datetime-local" class="pf-inp" id="pf_textans" value="${isEdit?esc(qdata.correct_options_multi):''}">
    </div>`;
  }

  // ── Marks ──
  h+=`<div class="pf-sec">
    <div class="pf-lbl">Marks *</div>
    <div class="marks-row">
      <input type="number" class="pf-inp" id="pf_marks" min="1" max="${available}" placeholder="e.g. 5" value="${isEdit?(qdata.marks||''):''}" oninput="marksHint(${origMarks})">
      <span class="marks-hint" id="marksHint">${available} available</span>
    </div>
  </div>`;

  // ── Actions ──
  h+=`<div class="pf-sec" style="border:none">
    <button class="btn-save" onclick="saveQ(${type},${isEdit?qdata.question_id:0})">
      <i class="ti ti-device-floppy"></i> ${isEdit?'Update Question':'Save Question'}
    </button>
    <button class="btn-cancel" onclick="cancelEdit()">Cancel</button>
  </div>`;

  document.getElementById('propsEmpty').style.display='none';
  document.getElementById('propsBody').innerHTML=
    '<div class="props-empty" id="propsEmpty" style="display:none"></div>'+h;

  // Post-render: highlight correct options for existing
  if(isEdit){
    if(type===1||type===3||type===5) hiCorrect(type);
    if(type===10||type===11||type===12) hiDynCorrect(type,qdata);
  }
}

// ── Static options (4 fixed) ─────────────────────
function optionsSection(type,qdata,isEdit,inputType,inputName,label){
  var h=`<div class="pf-sec"><div class="pf-lbl">${label}</div>`;
  var imgPrefix=(type===5)?'pf_imgopt':'pf_opt';
  var nm=(type===5)?'pf_img_correct':(type===3?'pf_multi[]':'pf_correct');
  for(var i=1;i<=4;i++){
    var oval=isEdit?esc(qdata['option_'+i]||''):'';
    var chk='';
    if(isEdit){
      if(type===1&&String(qdata.correct_option)===String(i)) chk='checked';
      if(type===5&&String(qdata.correct_option)===String(i)) chk='checked';
      if(type===3){ try{ var ma=JSON.parse(qdata.correct_options_multi||'[]'); if(ma.map(String).includes(String(i))) chk='checked'; }catch(e){} }
    }
    h+=`<div class="opt-row">
      <input type="${inputType}" name="${nm}" value="${i}" ${chk} id="optchk${i}" onchange="hiCorrect(${type})">
      <input type="text" class="pf-inp" id="${imgPrefix}${i}" placeholder="Option ${i}" value="${oval}">
    </div>`;
  }
  if(type===5){ h+='<small style="color:var(--text3);font-size:.72rem">Select radio next to the correct option</small>'; }
  h+='</div>';
  return h;
}

// ── Dynamic options (dropdown / radio / checkbox) ─
function dynamicOptionsSection(qdata,isEdit,kind,inputName){
  // Parse saved options from JSON in option_1 field
  var saved=[];
  if(isEdit && qdata.option_1){
    try{ saved=JSON.parse(qdata.option_1); }catch(e){ saved=qdata.option_1?[qdata.option_1]:[] }
  }
  if(!saved.length) saved=['','',''];

  var correctRaw=isEdit?(qdata.correct_options_multi||''):'';
  var correctArr=[];
  try{ correctArr=JSON.parse(correctRaw); }catch(e){ if(correctRaw) correctArr=[correctRaw]; }

  var h=`<div class="pf-sec">
    <div class="pf-lbl">Options <small style="color:var(--text3);font-weight:400;text-transform:none">(mark correct)</small></div>
    <div id="dynOpts">`;
  saved.forEach(function(ov,idx){
    var isCorrect=correctArr.includes(String(idx+1))||correctArr.includes(ov);
    var chk=isCorrect?'checked':'';
    var inp=kind==='select'?'radio':kind; // dropdown uses radio for correct
    h+=`<div class="opt-row" id="dynopt_${++optionCounter}">
      <input type="${inp==='checkbox'?'checkbox':'radio'}" name="${inputName}" value="${idx+1}" ${chk} onchange="hiDynOpts('${kind}')">
      <input type="text" class="pf-inp ${isCorrect?'correct-hi':''}" placeholder="Option ${idx+1}" value="${esc(ov)}">
      <button type="button" class="opt-del" onclick="removeDynOpt(this)"><i class="ti ti-x"></i></button>
    </div>`;
  });
  h+=`</div>
    <button type="button" class="btn-add-opt" onclick="addDynOpt('${kind}','${inputName}')"><i class="ti ti-plus"></i> Add Option</button>
  </div>`;
  return h;
}

function addDynOpt(kind,inputName){
  var container=document.getElementById('dynOpts');
  var rows=container.querySelectorAll('.opt-row');
  var idx=rows.length+1;
  var inp=kind==='checkbox'?'checkbox':'radio';
  var row=document.createElement('div');
  row.className='opt-row'; row.id='dynopt_'+(++optionCounter);
  row.innerHTML=`<input type="${inp}" name="${inputName}" value="${idx}" onchange="hiDynOpts('${kind}')">
    <input type="text" class="pf-inp" placeholder="Option ${idx}">
    <button type="button" class="opt-del" onclick="removeDynOpt(this)"><i class="ti ti-x"></i></button>`;
  container.appendChild(row);
}

function removeDynOpt(btn){
  var row=btn.closest('.opt-row');
  var container=document.getElementById('dynOpts');
  if(container.querySelectorAll('.opt-row').length>1) row.remove();
}

function hiDynOpts(kind){
  document.querySelectorAll('#dynOpts .opt-row').forEach(function(row){
    var chk=row.querySelector('input[type=radio],input[type=checkbox]');
    var inp=row.querySelector('.pf-inp');
    if(inp) inp.classList.toggle('correct-hi', chk&&chk.checked);
  });
}

function hiDynCorrect(type,qdata){
  // Already rendered with correct-hi class
}

function hiCorrect(type){
  var prefix=(type===5)?'pf_imgopt':'pf_opt';
  for(var i=1;i<=4;i++){
    var inp=document.getElementById(prefix+i);
    var chk=document.getElementById('optchk'+i);
    if(inp&&chk) inp.classList.toggle('correct-hi',chk.checked);
  }
}

function tfSel(){
  document.getElementById('tfT').classList.toggle('sel',document.querySelector('input[name="pf_tf"][value="True"]').checked);
  document.getElementById('tfF').classList.toggle('sel',document.querySelector('input[name="pf_tf"][value="False"]').checked);
}

function marksHint(origMarks){
  var available=assessmentTotalMarks-currentTotalMarks+origMarks;
  var entered=parseInt(document.getElementById('pf_marks').value)||0;
  var rem=available-entered;
  var el=document.getElementById('marksHint'); if(!el) return;
  if(rem<0){ el.textContent='Exceeds by '+Math.abs(rem); el.className='marks-hint err'; }
  else if(rem===0){ el.textContent='Exact'; el.className='marks-hint'; el.style.color='var(--green)'; }
  else { el.textContent=rem+' remaining'; el.className='marks-hint'+(rem<=5?' warn':''); }
}

function prevImg(input){
  var file=input.files[0]; if(!file) return;
  if(file.size>2*1024*1024){ showToast('Image too large (max 2MB)','error'); input.value=''; return; }
  var reader=new FileReader();
  reader.onload=function(e){ var p=document.getElementById('pf_imgprev'); p.innerHTML='<img src="'+e.target.result+'" class="img-prev">'; };
  reader.readAsDataURL(file);
}

function cancelEdit(){
  activeCardId=null; pendingType=null;
  document.querySelectorAll('.q-card').forEach(c=>c.classList.remove('active'));
  var p=document.getElementById('pendingCard'); if(p) p.remove();
  document.getElementById('propsChip').innerHTML='';
  document.getElementById('propsBody').innerHTML=`<div class="props-empty" id="propsEmpty"><i class="ti ti-arrow-left"></i><p>Drag a field type from the left panel, or click an existing question to edit.</p></div>`;
}

// ── Save question ────────────────────────────────
function saveQ(type,qid){
  var isEdit=qid>0;
  var qtext=(document.getElementById('pf_qtext')||{value:''}).value.trim();
  var marks=parseInt((document.getElementById('pf_marks')||{value:0}).value)||0;
  if(!qtext){ showToast('Question text is required','error'); return; }
  if(marks<1){ showToast('Marks must be at least 1','error'); return; }
  var origMarks=isEdit?(existingQuestions.find(x=>x.question_id===qid)||{marks:0}).marks:0;
  var available=assessmentTotalMarks-currentTotalMarks+origMarks;
  if(marks>available){ showToast('Only '+available+' mark(s) available','error'); return; }

  var btn=document.querySelector('.btn-save');
  btn.disabled=true;
  btn.innerHTML='<span style="display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;margin-right:6px"></span>'+(isEdit?'Updating…':'Saving…');

  var fd=new FormData();
  fd.append('formName',isEdit?'update_question':'create_question');
  fd.append('assessment_id',assessmentId);
  fd.append('question_type',type);
  fd.append('question_text',qtext);
  fd.append('question_marks',marks);
  if(isEdit) fd.append('question_id',qid);

  var ok=true;

  if(type===1){ // Single Choice
    var c=document.querySelector('input[name="pf_correct"]:checked');
    if(!c){ showToast('Select the correct option','error'); resetBtn(btn,isEdit); return; }
    for(var i=1;i<=4;i++){
      var v=val('pf_opt'+i); if(!v){ showToast('Fill all 4 options','error'); resetBtn(btn,isEdit); return; }
      fd.append('option_'+i,v);
    }
    fd.append('correct_option',c.value);

  } else if(type===2){ // True/False
    var tf=document.querySelector('input[name="pf_tf"]:checked');
    if(!tf){ showToast('Select True or False','error'); resetBtn(btn,isEdit); return; }
    fd.append('correct_option',tf.value);

  } else if(type===3){ // Multiple Choice
    var mc=document.querySelectorAll('input[name="pf_multi[]"]:checked');
    if(!mc.length){ showToast('Select at least one correct option','error'); resetBtn(btn,isEdit); return; }
    for(var i=1;i<=4;i++){
      var v=val('pf_opt'+i); if(!v){ showToast('Fill all 4 options','error'); resetBtn(btn,isEdit); return; }
      fd.append('option_'+i,v);
    }
    fd.append('correct_options_multi',JSON.stringify([...mc].map(x=>x.value)));

  } else if(type===4){ // Text Answer
    var ta=val('pf_textans').trim();
    if(!ta){ showToast('Enter expected answer','error'); resetBtn(btn,isEdit); return; }
    fd.append('correct_option',ta);

  } else if(type===5){ // Image-based
    var ic=document.querySelector('input[name="pf_img_correct"]:checked');
    if(!ic){ showToast('Select the correct option','error'); resetBtn(btn,isEdit); return; }
    for(var i=1;i<=4;i++){
      var v=val('pf_imgopt'+i); if(!v){ showToast('Fill all 4 options','error'); resetBtn(btn,isEdit); return; }
      fd.append('option_'+i,v);
    }
    fd.append('correct_option',ic.value);
    var imgFile=(document.getElementById('pf_imgfile')||{files:[]}).files[0];
    var ei=val('pf_existimg');
    if(imgFile) fd.append('question_image',imgFile);
    else if(ei) fd.append('existing_image',ei);
    else{ showToast('Upload a question image','error'); resetBtn(btn,isEdit); return; }

  } else if(type===6||type===8){ // Text / URL
    fd.append('option_1',val('pf_placeholder'));
    fd.append('correct_option',val('pf_textans'));

  } else if(type===7){ // Number
    fd.append('option_1',val('pf_nummin'));
    fd.append('option_2',val('pf_nummax'));
    fd.append('correct_option',val('pf_textans'));

  } else if(type===9){ // Textarea
    fd.append('option_1',val('pf_tarows')||'4');
    fd.append('option_2',val('pf_placeholder'));
    fd.append('correct_option',val('pf_textans'));

  } else if(type===10||type===11||type===12){ // Dropdown / Radio / Checkbox
    var rows=document.querySelectorAll('#dynOpts .opt-row');
    var opts=[]; var corrects=[];
    rows.forEach(function(row,idx){
      var txt=row.querySelector('.pf-inp'); var chk=row.querySelector('input[type=radio],input[type=checkbox]');
      var ov=txt?txt.value.trim():'';
      opts.push(ov);
      if(chk&&chk.checked) corrects.push(String(idx+1));
    });
    if(opts.some(o=>!o)){ showToast('Fill all option labels','error'); resetBtn(btn,isEdit); return; }
    if(!corrects.length){ showToast('Mark at least one correct option','error'); resetBtn(btn,isEdit); return; }
    fd.append('option_1',JSON.stringify(opts));
    fd.append('correct_options_multi',JSON.stringify(corrects));
    fd.append('correct_option',corrects[0]);

  } else if(type===13||type===14||type===15){ // Date / Time / DateTime
    fd.append('correct_option',val('pf_textans'));
  }

  fetch('includes/datacontrol.php',{method:'POST',body:fd})
    .then(r=>r.text())
    .then(function(resp){
      if(resp.trim()==='1'){
        showToast(isEdit?'Question updated!':'Question saved!','success');
        currentTotalMarks=isEdit?(currentTotalMarks-origMarks+marks):(currentTotalMarks+marks);
        if(!isEdit) questionCount++;
        updateStats();
        setTimeout(()=>location.reload(),900);
      } else {
        showToast('Error: '+resp.trim(),'error');
        resetBtn(btn,isEdit);
      }
    })
    .catch(function(){ showToast('Request failed','error'); resetBtn(btn,isEdit); });
}

function resetBtn(btn,isEdit){
  btn.disabled=false;
  btn.innerHTML='<i class="ti ti-device-floppy"></i> '+(isEdit?'Update Question':'Save Question');
}

// ── Delete ───────────────────────────────────────
function deleteQ(qid){
  if(!confirm('Delete this question?')) return;
  var fd=new FormData(); fd.append('formName','delete_question'); fd.append('question_id',qid);
  fetch('includes/datacontrol.php',{method:'POST',body:fd})
    .then(r=>r.text())
    .then(function(r){
      if(r.trim()==='1'){
        showToast('Deleted','success');
        var q=existingQuestions.find(x=>x.question_id===qid);
        if(q){ currentTotalMarks-=q.marks; questionCount--; updateStats(); }
        setTimeout(()=>location.reload(),800);
      } else showToast('Delete failed','error');
    });
}
</script>
</body>
</html>
<?php } else { header("Location: index.php"); } ?>
