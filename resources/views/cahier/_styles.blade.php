<style>
    .ct-wrap { max-width: 960px; }
    .ct-bar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
    .btn-ct { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px; background:var(--primary,#1a4fa0); color:#fff !important; text-decoration:none; border:none; font-size:13.5px; font-weight:600; cursor:pointer; font-family:inherit; }
    .btn-ct:hover { filter:brightness(1.05); }
    .btn-ct.ghost { background:var(--bg,#f4f6fb); color:var(--text,#1e2238) !important; border:1.5px solid var(--border,#e5e7eb); }
    .btn-ct.sm { padding:6px 12px; font-size:12px; }
    .btn-ct.danger { background:#fef2f2; color:#b91c1c !important; border:1.5px solid #fecaca; }

    .ct-card { background:var(--card,#fff); border:1px solid var(--border,#e5e7eb); border-radius:14px; box-shadow:var(--shadow-sm,0 2px 10px rgba(0,0,0,.04)); margin-bottom:14px; overflow:hidden; }
    .ct-head { display:flex; align-items:center; gap:12px; padding:14px 18px; border-bottom:1px solid var(--border,#eef1f5); flex-wrap:wrap; }
    .ct-date { display:flex; flex-direction:column; align-items:center; justify-content:center; background:var(--primary,#1a4fa0); color:#fff; border-radius:10px; min-width:52px; padding:6px 8px; }
    .ct-date .d { font-size:18px; font-weight:800; line-height:1; }
    .ct-date .m { font-size:10px; text-transform:uppercase; letter-spacing:.5px; }
    .ct-titre { font-weight:700; font-size:14.5px; color:var(--text,#1e2238); }
    .ct-sub { font-size:12px; color:var(--text-muted,#6b7280); margin-top:2px; }
    .ct-body { padding:16px 18px; }
    .ct-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted,#6b7280); margin-bottom:6px; }
    .ct-contenu { font-size:13.5px; line-height:1.6; color:var(--text,#1e2238); white-space:pre-wrap; }
    .ct-devoirs { margin-top:14px; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:12px 14px; }
    .ct-devoirs .ct-label { color:#b45309; }
    .ct-devoirs .txt { font-size:13.5px; line-height:1.55; white-space:pre-wrap; color:#1e2238; }
    .ct-remise { display:inline-flex; align-items:center; gap:6px; margin-top:8px; font-size:12px; font-weight:700; color:#b45309; background:#fef3c7; border-radius:20px; padding:4px 12px; }
    .badge-mat { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; background:#eef2ff; color:#3730a3; }

    .ct-field { margin-bottom:16px; }
    .ct-field label { display:block; font-size:12px; font-weight:700; color:var(--text,#1e2238); margin-bottom:6px; }
    .ct-field select, .ct-field input[type=date], .ct-field textarea, .ct-field input[type=text] {
        width:100%; padding:10px 13px; border:1.5px solid var(--border,#e5e7eb); border-radius:10px; font-family:inherit; font-size:13.5px; background:var(--bg,#fafafa); color:var(--text,#1e2238); }
    .ct-field textarea { resize:vertical; min-height:110px; line-height:1.5; }
    .ct-field select:focus, .ct-field textarea:focus, .ct-field input:focus { outline:none; border-color:var(--primary,#1a4fa0); background:var(--card,#fff); }
    .ct-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    @media(max-width:640px){ .ct-grid2 { grid-template-columns:1fr; } }

    .alert-ok { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; }
    .alert-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; }
    .empty-state { text-align:center; padding:56px 20px; color:var(--text-muted,#6b7280); }
    .empty-state i { font-size:46px; opacity:.3; display:block; margin-bottom:14px; }
    .chip-row { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end; }
    .chip-row select { padding:9px 12px; border:1.5px solid var(--border,#e5e7eb); border-radius:9px; font-family:inherit; font-size:13px; background:var(--bg,#fafafa); color:var(--text,#1e2238); }
</style>
