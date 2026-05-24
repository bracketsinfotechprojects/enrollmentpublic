<?php
//  error_reporting(0);     
?>
        <?php $CRM_ASSET_BASE = 'crm/html/template/assets'; ?>
        <script>
            (function () {
                var THEME_KEY = "__THEME_CONFIG__";
                var MINI_KEY = "__CRM_MINI_SIDEBAR__";

                function safeParse(json) {
                    try { return JSON.parse(json); } catch (e) { return null; }
                }

                function safeStringify(obj) {
                    try { return JSON.stringify(obj); } catch (e) { return null; }
                }

                function applyMiniToThemeConfig(raw, isMini) {
                    var cfg = typeof raw === 'string' ? safeParse(raw) : raw;
                    if (!cfg || typeof cfg !== 'object') return null;
                    if (!cfg.sidenav || typeof cfg.sidenav !== 'object') cfg.sidenav = {};
                    cfg.sidenav.size = isMini ? 'mini' : 'default';
                    return cfg;
                }

                try {
                    var savedTheme = localStorage.getItem(THEME_KEY);
                    var savedMini = localStorage.getItem(MINI_KEY) === '1';
                    if (savedTheme) {
                        var merged = applyMiniToThemeConfig(savedTheme, savedMini);
                        if (merged) {
                            var mergedStr = safeStringify(merged);
                            if (mergedStr) savedTheme = mergedStr;
                            try { localStorage.setItem(THEME_KEY, savedTheme); } catch (e) {}
                        }
                    }
                    if (savedTheme && !sessionStorage.getItem(THEME_KEY)) {
                        sessionStorage.setItem(THEME_KEY, savedTheme);
                    }
                } catch (e) {}

                try {
                    var _setItem = sessionStorage.setItem.bind(sessionStorage);
                    var _removeItem = sessionStorage.removeItem.bind(sessionStorage);
                    sessionStorage.setItem = function (k, v) {
                        _setItem(k, v);
                        if (k === THEME_KEY) {
                            try { localStorage.setItem(THEME_KEY, v); } catch (e) {}
                        }
                    };
                    sessionStorage.removeItem = function (k) {
                        _removeItem(k);
                        if (k === THEME_KEY) {
                            try { localStorage.removeItem(THEME_KEY); } catch (e) {}
                        }
                    };
                } catch (e) {}

                document.addEventListener('DOMContentLoaded', function () {
                    try {
                        var isMini = localStorage.getItem(MINI_KEY) === '1';
                        if (isMini) {
                            document.body.classList.add('mini-sidebar');
                            var t1 = document.getElementById('toggle_btn');
                            var t2 = document.getElementById('toggle_btn2');
                            if (t1) t1.classList.remove('active');
                            if (t2) t2.classList.remove('active');
                            var headerLeft = document.querySelector('.header-left');
                            if (headerLeft) headerLeft.classList.remove('active');
                        }
                    } catch (e) {}

                    document.addEventListener('click', function (ev) {
                        var el = ev.target;
                        if (!el) return;
                        var btn = el.closest ? el.closest('#toggle_btn, #toggle_btn2') : null;
                        if (!btn) return;

                        setTimeout(function () {
                            try {
                                var nowMini = document.body.classList.contains('mini-sidebar');
                                localStorage.setItem(MINI_KEY, nowMini ? '1' : '0');

                                var currentTheme = sessionStorage.getItem(THEME_KEY) || localStorage.getItem(THEME_KEY);
                                if (currentTheme) {
                                    var updated = applyMiniToThemeConfig(currentTheme, nowMini);
                                    var updatedStr = safeStringify(updated);
                                    if (updatedStr) {
                                        sessionStorage.setItem(THEME_KEY, updatedStr);
                                        localStorage.setItem(THEME_KEY, updatedStr);
                                    }
                                }
                            } catch (e) {}
                        }, 0);
                    }, true);
                });
            })();
        </script>
        <link rel="stylesheet" href="<?php echo $CRM_ASSET_BASE; ?>/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo $CRM_ASSET_BASE; ?>/plugins/tabler-icons/tabler-icons.min.css">
        <link rel="stylesheet" href="<?php echo $CRM_ASSET_BASE; ?>/plugins/simplebar/simplebar.min.css">
        <link rel="stylesheet" href="<?php echo $CRM_ASSET_BASE; ?>/plugins/datatables/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="<?php echo $CRM_ASSET_BASE; ?>/plugins/daterangepicker/daterangepicker.css">
        <link rel="stylesheet" href="<?php echo $CRM_ASSET_BASE; ?>/plugins/select2/css/select2.min.css">
        <link rel="stylesheet" href="<?php echo $CRM_ASSET_BASE; ?>/css/style.css" id="app-style">
        <link rel="stylesheet" href="assets/css/panel.css?v=<?php echo time(); ?>">
        <!-- SweetAlert2 (CDN): modals / confirmations app-wide -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/css/bootstrap-select.min.css" />

        <style>
            @media (min-width: 992px) {
                body.mini-sidebar .page-wrapper {
                    margin-left: var(--sidenav-width-sm) !important;
                }
                body.mini-sidebar.expand-menu .page-wrapper {
                    margin-left: var(--sidenav-width) !important;
                }
                body.mini-sidebar .navbar-header {
                    margin-left: var(--sidenav-width-sm) !important;
                }
                body.mini-sidebar.expand-menu .navbar-header {
                    margin-left: var(--sidenav-width) !important;
                }
            }
        </style>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

        <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>    

         <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
        
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
  <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
  </symbol>
  <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
  </symbol>
  <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
  </symbol>
</svg>

<?php include('popup_modals.php'); ?>