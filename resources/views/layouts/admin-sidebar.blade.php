{{-- ══ 管理画面 サイドバー（常時表示・全ページ1クリック移動）══ --}}
<aside class="admin-sidebar" aria-label="管理メニュー">
    <nav class="admin-sidebar__nav">
        <ul>
            <li class="admin-sidebar__section-label">運営</li>
            <li>
                <a href="{{ route('admin.ops') }}"
                   class="{{ request()->routeIs('admin.ops*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line" aria-hidden="true"></i>運営ダッシュボード
                </a>
            </li>
            <li>
                <a href="{{ route('admin.login-history') }}"
                   class="{{ request()->routeIs('admin.login-history*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>ログイン履歴
                </a>
            </li>
            <li>
                <a href="{{ route('admin.audit.email') }}"
                   class="{{ request()->routeIs('admin.audit.email*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i>メール操作ログ
                </a>
            </li>

            <li class="admin-sidebar__section-label">受付</li>
            <li>
                <a href="{{ route('admin.checkin.index') }}"
                   class="{{ request()->routeIs('admin.checkin.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i>受付チェックイン
                </a>
            </li>
            <li>
                <a href="{{ route('admin.checkin.guests') }}"
                   class="{{ request()->routeIs('admin.checkin.guests') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-user" aria-hidden="true"></i>受付一覧
                </a>
            </li>
            <li>
                <a href="{{ route('admin.audit.checkin') }}"
                   class="{{ request()->routeIs('admin.audit.checkin*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>操作ログ
                </a>
            </li>

            <li class="admin-sidebar__section-label">ゲスト</li>
            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>ゲスト一覧
                </a>
            </li>
            <li>
                <a href="{{ route('admin.rsvp') }}"
                   class="{{ request()->routeIs('admin.rsvp*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>回答状況
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users') }}"
                   class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-pen" aria-hidden="true"></i>ユーザー管理
                </a>
            </li>
            <li>
                <a href="{{ route('admin.seating') }}"
                   class="{{ request()->routeIs('admin.seating*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chair" aria-hidden="true"></i>席次表
                </a>
            </li>

            <li class="admin-sidebar__section-label">コンテンツ</li>
            <li>
                <a href="{{ route('admin.news') }}"
                   class="{{ request()->routeIs('admin.news*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>お知らせ
                </a>
            </li>
            <li>
                <a href="{{ route('admin.profiles') }}"
                   class="{{ request()->routeIs('admin.profiles') ? 'active' : '' }}">
                    <i class="fa-solid fa-heart" aria-hidden="true"></i>プロフィール
                </a>
            </li>
            <li>
                <a href="{{ route('admin.tasks') }}"
                   class="{{ request()->routeIs('admin.tasks*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>当日の役割
                </a>
            </li>
            <li>
                <a href="{{ route('admin.program') }}"
                   class="{{ request()->routeIs('admin.program*') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-ol" aria-hidden="true"></i>プログラム
                </a>
            </li>
            <li>
                <a href="{{ route('admin.faq') }}"
                   class="{{ request()->routeIs('admin.faq*') ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-question" aria-hidden="true"></i>Q&amp;A
                </a>
            </li>
            <li>
                <a href="{{ route('admin.gallery') }}"
                   class="{{ request()->routeIs('admin.gallery*') ? 'active' : '' }}">
                    <i class="fa-solid fa-images" aria-hidden="true"></i>ギャラリー
                </a>
            </li>
            <li>
                <a href="{{ route('admin.guestbook') }}"
                   class="{{ request()->routeIs('admin.guestbook*') ? 'active' : '' }}">
                    <i class="fa-solid fa-comment-dots" aria-hidden="true"></i>ゲストブック
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reminders') }}"
                   class="{{ request()->routeIs('admin.reminders*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-open-text" aria-hidden="true"></i>リマインダーメール
                </a>
            </li>

            <li class="admin-sidebar__section-label">サイト全体</li>
            <li>
                <a href="{{ route('admin.media') }}"
                   class="{{ request()->routeIs('admin.media*') ? 'active' : '' }}">
                    <i class="fa-solid fa-photo-film" aria-hidden="true"></i>メディア
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings') }}"
                   class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear" aria-hidden="true"></i>設定
                </a>
            </li>
        </ul>
    </nav>
</aside>
