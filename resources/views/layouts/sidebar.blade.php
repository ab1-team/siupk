{{-- @php

    function active($curent, ...$_url)
    {
        $jumlah_url = count(request()->segments());
        $url = request()->segment($jumlah_url);

        if ($curent == $url) {
            return 'active';
        }

        if (in_array($url, $_url)) {
            return 'active';
        }

        if (in_array(request()->segment($jumlah_url - 1), $_url)) {
            return 'active';
        }

        return '';
    }
@endphp --}}

<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3   bg-gradient-dark"
    id="sidenav-main" data-color="success">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand text-center" href="/dashboard">
            <span class="ms-1 font-weight-bold text-white" id="nama_lembaga_sort">
                {{ Session::get('nama_lembaga') }}
            </span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item mb-2 mt-0">
                <a href="/profil" class="nav-link text-white">
                    <img src="{{ asset('storage/profil/' . Session::get('foto')) }}" class="avatar" id="profil_avatar">
                    <span class="nav-link-text ms-2 ps-1 nama_user">{{ Session::get('nama') }}</span>
                </a>
            </li>
            <hr class="horizontal light mt-0">
            @if(auth()->user()->level == 3)
                @include('layouts.menu_pengawas')
            @else
                @include('layouts.menu', ['parent_menu' => Session::get('menu')])
            @endif
        </ul>
    </div>
</aside>
