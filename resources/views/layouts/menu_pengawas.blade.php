@php
    $checkActive = function($link) {
        $urlPath = ltrim($link, '/');
        if (empty($urlPath) || $link === '#' || $link === 'javascript:;') {
            return false;
        }
        return request()->is($urlPath) || request()->is($urlPath . '/*');
    };

    $menuPengawas = [
        [
            'id'    => 'pw-1',
            'title' => 'Dashboard',
            'icon'  => 'fas fa-chart-line',
            'link'  => '/dashboard',
            'child' => [],
        ],
        [
            'id'    => 'pw-2',
            'title' => 'Form Pengawas',
            'icon'  => 'fas fa-clipboard-check',
            'link'  => '/form_pengawas',
            'child' => [],
        ],
        [
            'id'    => 'pw-3',
            'title' => 'Laporan',
            'icon'  => 'fas fa-file-invoice-dollar',
            'link'  => '/pelaporan',
            'child' => [],
        ],
    ];
@endphp

<ul class="navbar-nav">
    @foreach($menuPengawas as $item)
        @php
            $isActive = $checkActive($item['link']);
            $hasActiveChild = false;
            foreach ($item['child'] as $child) {
                if ($checkActive($child['link'])) {
                    $hasActiveChild = true;
                    break;
                }
            }
        @endphp

        @if(empty($item['child']))
            {{-- Menu tanpa submenu --}}
            <li class="nav-item">
                <a class="nav-link {{ $isActive ? 'active' : '' }}"
                   href="{{ url($item['link']) }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="{{ $item['icon'] }} text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">{{ $item['title'] }}</span>
                </a>
            </li>
        @else
            {{-- Menu dengan submenu --}}
            <li class="nav-item">
                <a class="nav-link {{ $hasActiveChild ? 'active' : '' }} menu-toggle"
                   href="javascript:;"
                   data-target="submenu-{{ $item['id'] }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="{{ $item['icon'] }} text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">{{ $item['title'] }}</span>
                    <i class="fas fa-chevron-down menu-arrow"></i>
                </a>

                <div class="sidenav-submenu {{ $hasActiveChild ? 'open' : '' }}" id="submenu-{{ $item['id'] }}">
                    <ul class="nav ms-4 ps-3">
                        @foreach($item['child'] as $child)
                            @php
                                $childActive = $checkActive($child['link']);
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link {{ $childActive ? 'active' : '' }}"
                                   href="{{ url($child['link']) }}">
                                    <i class="far fa-circle text-secondary opacity-5 me-2" style="font-size: 6px;"></i>
                                    <span class="sidenav-normal">{{ $child['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
        @endif
    @endforeach
</ul>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.menu-toggle').forEach(function (toggle) {
        var targetId = toggle.getAttribute('data-target');
        var submenu = document.getElementById(targetId);
        if (submenu && submenu.classList.contains('open')) {
            toggle.classList.add('open');
        }
    });

    document.querySelectorAll('.menu-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var targetId = this.getAttribute('data-target');
            var submenu = document.getElementById(targetId);
            if (!submenu) return;

            var isOpen = submenu.classList.contains('open');

            var parentUl = this.closest('ul');
            if (parentUl) {
                parentUl.querySelectorAll(':scope > li > .menu-toggle').forEach(function (sibling) {
                    var sibId = sibling.getAttribute('data-target');
                    var sibMenu = document.getElementById(sibId);
                    if (sibMenu && sibMenu !== submenu) {
                        sibMenu.classList.remove('open');
                        sibling.classList.remove('open');
                    }
                });
            }

            submenu.classList.toggle('open', !isOpen);
            this.classList.toggle('open', !isOpen);
        });
    });
});
</script>
