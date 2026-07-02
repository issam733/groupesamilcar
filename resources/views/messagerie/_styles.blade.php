<style>
    .msg-wrap { max-width: 920px; }
    .msg-toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:10px; flex-wrap:wrap; }
    .btn-msg { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px; background:var(--primary,#1a4fa0); color:#fff !important; text-decoration:none; border:none; font-size:13.5px; font-weight:600; cursor:pointer; font-family:inherit; }
    .btn-msg:hover { filter:brightness(1.05); }
    .btn-msg.ghost { background:var(--bg,#f4f6fb); color:var(--text,#1e2238) !important; border:1.5px solid var(--border,#e5e7eb); }

    .conv-list { display:flex; flex-direction:column; gap:10px; }
    .conv-item { display:flex; gap:14px; align-items:center; background:var(--card,#fff); border:1px solid var(--border,#e5e7eb); border-radius:13px; padding:14px 16px; text-decoration:none; color:inherit; box-shadow:var(--shadow-sm,0 2px 10px rgba(0,0,0,.04)); transition:transform .15s; }
    .conv-item:hover { transform:translateY(-1px); }
    .conv-item.unread { border-left:4px solid var(--primary,#1a4fa0); }
    .avatar { width:46px; height:46px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:15px; color:#fff; flex-shrink:0; }
    .conv-main { flex:1; min-width:0; }
    .conv-name { font-size:14px; font-weight:700; color:var(--text,#1e2238); display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .conv-snippet { font-size:12.5px; color:var(--text-muted,#6b7280); margin-top:3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .conv-meta { text-align:right; flex-shrink:0; display:flex; flex-direction:column; align-items:flex-end; }
    .conv-time { font-size:11px; color:var(--text-muted,#6b7280); }
    .badge-unread { display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:20px; padding:0 6px; border-radius:10px; background:#ef4444; color:#fff; font-size:11px; font-weight:700; margin-top:6px; }
    .role-badge { font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:.3px; color:#fff; }

    .thread { display:flex; flex-direction:column; gap:12px; padding:8px 0 16px; }
    .bubble-row { display:flex; }
    .bubble-row.me { justify-content:flex-end; }
    .bubble { max-width:74%; padding:11px 15px; border-radius:14px; font-size:13.5px; line-height:1.5; white-space:pre-wrap; word-wrap:break-word; }
    .bubble.them { background:var(--card,#fff); border:1px solid var(--border,#e5e7eb); color:var(--text,#1e2238); border-bottom-left-radius:4px; }
    .bubble.me { background:var(--primary,#1a4fa0); color:#fff; border-bottom-right-radius:4px; }
    .bubble-time { font-size:10.5px; margin-top:5px; opacity:.7; }

    .reply-bar { display:flex; gap:10px; align-items:flex-end; background:var(--card,#fff); border:1px solid var(--border,#e5e7eb); border-radius:14px; padding:12px; box-shadow:var(--shadow-sm,0 2px 10px rgba(0,0,0,.04)); position:sticky; bottom:12px; }
    .reply-bar textarea { flex:1; border:none; outline:none; resize:none; font-family:inherit; font-size:13.5px; background:transparent; color:var(--text,#1e2238); max-height:140px; }

    .compose-card { background:var(--card,#fff); border:1px solid var(--border,#e5e7eb); border-radius:14px; padding:20px 22px; box-shadow:var(--shadow-sm,0 2px 10px rgba(0,0,0,.04)); }
    .recip-group { margin-bottom:16px; }
    .recip-group-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--text-muted,#6b7280); margin-bottom:8px; display:flex; align-items:center; gap:10px; }
    .recip-group-title a { font-size:11px; color:var(--primary,#1a4fa0); cursor:pointer; font-weight:600; text-decoration:none; }
    .recip-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:8px; }
    .recip-chip { display:flex; align-items:center; gap:9px; padding:9px 12px; border:1.5px solid var(--border,#e5e7eb); border-radius:10px; font-size:13px; cursor:pointer; background:var(--bg,#f9fafb); }
    .recip-chip input { accent-color:var(--primary,#1a4fa0); width:16px; height:16px; }
    .recip-chip.checked { border-color:var(--primary,#1a4fa0); background:#eef2ff; }
    .compose-textarea { width:100%; min-height:130px; border:1.5px solid var(--border,#e5e7eb); border-radius:11px; padding:12px 14px; font-family:inherit; font-size:13.5px; resize:vertical; outline:none; margin-top:8px; background:var(--bg,#fafafa); color:var(--text,#1e2238); }
    .compose-textarea:focus { border-color:var(--primary,#1a4fa0); background:var(--card,#fff); }

    .alert-ok { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; }
    .alert-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; }
    .empty-state { text-align:center; padding:56px 20px; color:var(--text-muted,#6b7280); }
    .empty-state i { font-size:46px; opacity:.3; display:block; margin-bottom:14px; }
</style>
