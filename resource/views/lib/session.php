<?php
// セッション開始（重複防止）
function start_session_once()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
