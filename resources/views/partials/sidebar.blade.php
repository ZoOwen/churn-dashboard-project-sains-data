<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo menu-text">DataNexus</div>
        <button class="sidebar-toggle" id="toggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>

   <nav class="sidebar-nav">
    <ul>
        <li class="active">
            <a href="#">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Overview</span>
            </a>
        </li>

        <li>
            <a href="{{ route('datasets.index') }}"><i class="fas fa-database"></i> <span class="menu-text">Datasets</span></a>
        </li>

        <!-- DASHBOARD V1 -->
        <li class="has-sub">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-flask"></i>
                <span class="menu-text">Dashboard V1</span>
                <i class="fas fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="#v11">Menu 1</a></li>
                <li><a href="#v12">Menu 2</a></li>
            </ul>
        </li>

        <!-- DASHBOARD V2 -->
        <li class="has-sub">
            <a href="#" class="submenu-toggle">
                <i class="fas fa-flask"></i>
                <span class="menu-text">Dashboard V2</span>
                <i class="fas fa-chevron-right arrow"></i>
            </a>
            <ul class="submenu">
                <li><a href="{{ route('dashboardv2.menu1') }}">Menu 1</a></li>
                <li><a href="#v22">Menu 2</a></li>
            </ul>
        </li>
    </ul>

    <div class="divider"></div>

    <ul>
        <li><a href="#"><i class="fas fa-book"></i> <span class="menu-text">Notebooks</span></a></li>
        <li><a href="#"><i class="fas fa-project-diagram"></i> <span class="menu-text">Pipelines</span></a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i> <span class="menu-text">Monitoring</span></a></li>
        <li><a href="#"><i class="fas fa-cogs"></i> <span class="menu-text">Settings</span></a></li>
    </ul>
</nav>

</aside>
