<style>
    .header {
        position: sticky;
        top: 12px;
        z-index: 10;
        margin: 12px 16px 0;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid var(--ring, rgba(148, 163, 184, .3));
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .07);
        transition: all 0.2s ease;
    }
    
    .brand {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .logo {
        font-weight: 800;
        letter-spacing: .2px;
        font-size: 18px;
        color: var(--brand, #1f2937);
        background: linear-gradient(135deg, var(--brand-2, #8B9DC3) 0%, var(--brand, #1f2937) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        padding: 4px 8px;
    }
    
    .nav {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .pill {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid var(--ring, rgba(148, 163, 184, .3));
        text-decoration: none;
        color: var(--text, #1e293b);
        background: rgba(248, 250, 252, 0.8);
        transition: all 0.2s ease;
        font-size: 14px;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }
    
    .pill::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: left 0.5s ease;
    }
    
    .pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: var(--brand-2, #8B9DC3);
    }
    
    .pill:hover::before {
        left: 100%;
    }
    
    .pill.active {
        background: var(--brand-2, #8B9DC3);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(139, 157, 195, 0.3);
    }
    
    .pill.primary {
        background: var(--brand, #1f2937);
        color: #fff;
        border-color: transparent;
        font-weight: 600;
    }
    
    .pill.primary:hover {
        background: var(--brand-2, #8B9DC3);
        box-shadow: 0 6px 20px rgba(139, 157, 195, 0.4);
    }
    
    .pill:focus-visible {
        outline: 2px solid var(--brand-2, #8B9DC3);
        outline-offset: 2px;
    }
    
    .server-time {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--muted, #475569);
        font-size: 12px;
        font-weight: 500;
        padding: 6px 12px;
        background: rgba(248, 250, 252, 0.6);
        border: 1px solid var(--ring, rgba(148, 163, 184, .3));
        border-radius: 999px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    .menu-toggle {
        display: none;
        background: transparent;
        border: none;
        padding: 8px;
        border-radius: 8px;
        cursor: pointer;
        color: var(--text, #1e293b);
    }
    
    .menu-toggle:hover {
        background: rgba(248, 250, 252, 0.8);
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .header {
            margin: 8px;
            padding: 10px 16px;
        }
        
        .brand {
            gap: 8px;
        }
        
        .logo {
            font-size: 16px;
        }
        
        .nav {
            display: none;
        }
        
        .menu-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .server-time {
            font-size: 10px;
            padding: 4px 8px;
            min-width: fit-content;
        }
        
        /* Mobile menu overlay - using :has() selector */
        .header:has(.menu-toggle[aria-expanded="true"]) .nav,
        .header.menu-open .nav {
            display: flex !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(30px) saturate(180%);
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border: none;
            border-radius: 0;
            padding: 40px 20px;
            z-index: 9999;
            box-shadow: none;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Mobile menu pills */
        .header:has(.menu-toggle[aria-expanded="true"]) .nav .pill,
        .header.menu-open .nav .pill {
            padding: 12px 20px;
            font-size: 16px;
            min-width: 160px;
            max-width: 200px;
            text-align: center;
            margin-bottom: 0;
            background: transparent;
            border: 1px solid var(--ring, rgba(148, 163, 184, .3));
        }
        
        /* Close button overlay */
        .header:has(.menu-toggle[aria-expanded="true"])::after,
        .header.menu-open::after {
            content: '✕';
            position: fixed;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: rgba(0, 0, 0, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: var(--text, #1e293b);
            z-index: 10000;
            cursor: pointer;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
    }
    
    /* Extra small screens */
    @media (max-width: 480px) {
        .header {
            margin: 6px;
            padding: 8px 12px;
        }
        
        .server-time span:first-child {
            display: none; /* Hide clock emoji on very small screens */
        }
        
        .server-time {
            font-size: 9px;
            padding: 3px 6px;
        }
        
        .logo {
            font-size: 14px;
        }
        
        .menu-toggle {
            width: 36px;
            height: 36px;
        }
        
        /* Smaller mobile menu pills */
        .header:has(.menu-toggle[aria-expanded="true"]) .nav .pill,
        .header.menu-open .nav .pill {
            padding: 10px 16px;
            font-size: 14px;
            min-width: 140px;
            max-width: 180px;
        }
        
        /* Adjust gap for smaller screens */
        .header:has(.menu-toggle[aria-expanded="true"]) .nav,
        .header.menu-open .nav {
            gap: 10px;
            padding: 30px 15px;
        }
    }
    
    /* Animation for mobile menu */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .header:has(.menu-toggle[aria-expanded="true"]) .nav {
        animation: slideInUp 0.2s ease-out;
    }
</style>

<header class="header" role="banner">
    <div class="brand">
        <div class="logo" aria-label="Arkturian Admin">AA</div>
        <nav class="nav" aria-label="Primary navigation">
            <a class="pill" href="index.php">Dashboard</a>
            <a class="pill" href="logs.php">Server Logs</a>
            <a class="pill" href="app_logs.php">App Logs</a>
            <a class="pill" href="server_admin.php">Server Admin</a>
            <a class="pill" href="system_alerts.php">System Alerts</a>
            <a class="pill" href="collections.php">Collections</a>
            <a class="pill" href="ai.php">AI Tools</a>
            <a class="pill" href="dialog.php">Dialog</a>
            <a class="pill" href="storage.php">Storage</a>
            <a class="pill" href="api.php">API Inspector</a>
            <a class="pill" href="status.php">API Status</a>
            <a class="pill primary" href="claude.php">Claude</a>
        </nav>
    </div>
    <div class="nav-right">
        <div class="server-time" title="Server Time">
            <span>🕐</span>
            <span id="server-clock"><?= date('H:i:s T') ?></span>
        </div>
        <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M4 6h16v2H4V6zm0 5h16v2H4v-2zm0 5h16v2H4v-2z"/>
            </svg>
        </button>
    </div>
</header>

<script>
    // Enhanced menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Highlight active page
        const links = document.querySelectorAll('.nav .pill');
        const currentPage = window.location.pathname.split('/').pop();
        
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPage || (currentPage === '' && href === 'index.php')) {
                link.classList.add('active');
            }
        });
        
        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.nav');
        const header = document.querySelector('.header');
        
        if (menuToggle) {
            // Toggle menu
            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
                menuToggle.setAttribute('aria-expanded', !isExpanded);
                
                // Add/remove class for browser compatibility
                if (!isExpanded) {
                    header.classList.add('menu-open');
                    document.body.style.overflow = 'hidden';
                } else {
                    header.classList.remove('menu-open');
                    document.body.style.overflow = '';
                }
            });
            
            // Close mobile menu when clicking on a link
            nav.addEventListener('click', function(e) {
                if (e.target.classList.contains('pill')) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    header.classList.remove('menu-open');
                    document.body.style.overflow = '';
                }
            });
            
            // Close mobile menu when clicking on overlay or close button
            document.addEventListener('click', function(e) {
                const isMenuExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
                if (isMenuExpanded) {
                    // Check if click is on the overlay (nav element) but not on a pill
                    if (e.target === nav || e.target === header.querySelector('::after')) {
                        menuToggle.setAttribute('aria-expanded', 'false');
                        header.classList.remove('menu-open');
                        document.body.style.overflow = '';
                    }
                }
            });
            
            // Handle close button clicks and overlay clicks
            document.addEventListener('click', function(e) {
                const isMenuExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
                if (isMenuExpanded) {
                    // Check if click is in the close button area (top-right corner)
                    const clickX = e.clientX;
                    const clickY = e.clientY;
                    
                    // Close button area (top-right 70x70 pixels)
                    if (clickX > window.innerWidth - 70 && clickY < 70) {
                        menuToggle.setAttribute('aria-expanded', 'false');
                        header.classList.remove('menu-open');
                        document.body.style.overflow = '';
                        e.preventDefault();
                        return;
                    }
                    
                    // Close if clicking on the overlay background (not on a pill)
                    if (e.target === nav && !e.target.classList.contains('pill')) {
                        menuToggle.setAttribute('aria-expanded', 'false');
                        header.classList.remove('menu-open');
                        document.body.style.overflow = '';
                    }
                }
            });
            
            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    header.classList.remove('menu-open');
                    document.body.style.overflow = '';
                }
            });
        }
        
        // Update server time every second
        function updateServerTime() {
            const clock = document.getElementById('server-clock');
            if (clock) {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    hour12: false,
                    timeZoneName: 'short'
                });
                clock.textContent = timeString;
            }
        }
        
        // Update time immediately and then every second
        updateServerTime();
        setInterval(updateServerTime, 1000);
    });
</script>
