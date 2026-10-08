@extends('layouts.base')

@section('content')
    <div class="nav-wrapper position-relative end-0">
        <ul class="nav nav-pills nav-fill p-1" role="tablist">
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 {{ $status == 'p' ? 'active' : '' }}" data-bs-toggle="tab" href="#Proposal"
                    role="tab" aria-controls="Proposal" aria-selected="true">
                    <span class="material-icons align-middle mb-1">
                        note_add
                    </span>
                    Proposal (P)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 {{ $status == 'v' ? 'active' : '' }}" data-bs-toggle="tab"
                    href="#Verified" role="tab" aria-controls="Verified" aria-selected="false">
                    <span class="material-icons align-middle mb-1">
                        event_available
                    </span>
                    Verified (V)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 {{ $status == 'w' ? 'active' : '' }}" data-bs-toggle="tab" href="#Waiting"
                    role="tab" aria-controls="Waiting" aria-selected="false">
                    <span class="material-icons align-middle mb-1">
                        history
                    </span>
                    Waiting (W)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 {{ $status == 'a' ? 'active' : '' }}" data-bs-toggle="tab" href="#Aktif"
                    role="tab" aria-controls="Aktif" aria-selected="false">
                    <span class="material-icons align-middle mb-1">
                        import_export
                    </span>
                    Aktif (A)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 {{ $status == 'l' ? 'active' : '' }}" data-bs-toggle="tab" href="#Lunas"
                    role="tab" aria-controls="Lunas" aria-selected="false">
                    <span class="material-icons align-middle mb-1">
                        cloud_done
                    </span>
                    Lunas (L)
                </a>
            </li>
        </ul>

        <div class="tab-content mt-2">
            <div class="tab-pane fade {{ $status == 'p' ? 'show active' : '' }}" id="Proposal" role="tabpanel"
                aria-labelledby="Proposal">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-flush table-hover table-click" width="100%" id="TbProposal">
                                <thead>
                                    <tr>
                                        <th>Nama Anggota P</th>
                                        <th>Alamat</th>
                                        <th>Tgl Pengajuan</th>
                                        <th>Pengajuan</th>
                                        <th>Jasa/Jangka</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade {{ $status == 'v' ? 'show active' : '' }}" id="Verified" role="tabpanel"
                aria-labelledby="Verified">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-flush table-hover table-click" width="100%" id="TbVerified">
                                <thead>
                                    <tr>
                                        <th>Nama Anggota</th>
                                        <th>Alamat</th>
                                        <th>Tgl Verified</th>
                                        <th>Verifikasi</th>
                                        <th>Jasa/Jangka</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade {{ $status == 'w' ? 'show active' : '' }}" id="Waiting" role="tabpanel"
                aria-labelledby="Waiting">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-flush table-hover table-click" width="100%" id="TbWaiting">
                                <thead>
                                    <tr>
                                        <th>Nama Anggota</th>
                                        <th>Alamat</th>
                                        <th>Tgl Waiting</th>
                                        <th>Alokasi</th>
                                        <th>Jasa/Jangka</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade {{ $status == 'a' ? 'show active' : '' }}" id="Aktif" role="tabpanel"
                aria-labelledby="Aktif">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-flush table-hover table-click" width="100%" id="TbAktif">
                                <thead>
                                    <tr>
                                        <th>Nama Anggota</th>
                                        <th>Alamat</th>
                                        <th>Tgl Cair</th>
                                        <th>Alokasi</th>
                                        <th>Jasa/Jangka</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade {{ $status == 'l' ? 'show active' : '' }}" id="Lunas" role="tabpanel"
                aria-labelledby="Lunas">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-flush table-hover table-click" width="100%" id="TbLunas">
                                <thead>
                                    <tr>
                                        <th>Nama Anggota</th>
                                        <th>Alamat</th>
                                        <th>Tgl Cair</th>
                                        <th>Verifikasi</th>
                                        <th>Jasa/Jangka</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var tbProposal = CreateTable('#TbProposal', '/perguliran_i/proposal', [{
            data: 'anggota.namadepan',
            name: 'anggota.namadepan'
        }, {
            data: 'anggota.alamat',
            name: 'anggota.alamat'
        }, {
            data: 'tgl_proposal',
            name: 'tgl_proposal'
        }, {
            data: 'proposal',
            name: 'proposal'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        var tbVerified = CreateTable('#TbVerified', '/perguliran_i/verified', [{
            data: 'anggota.namadepan',
            name: 'anggota.namadepan'
        }, {
            data: 'anggota.alamat',
            name: 'anggota.alamat'
        }, {
            data: 'tgl_verifikasi',
            name: 'tgl_verifikasi'
        }, {
            data: 'verifikasi',
            name: 'verifikasi'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        var tbWaiting = CreateTable('#TbWaiting', '/perguliran_i/waiting', [{
            data: 'anggota.namadepan',
            name: 'anggota.namadepan'
        }, {
            data: 'anggota.alamat',
            name: 'anggota.alamat'
        }, {
            data: 'tgl_tunggu',
            name: 'tgl_tunggu'
        }, {
            data: 'alokasi',
            name: 'alokasi'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        var tbAktif = CreateTable('#TbAktif', '/perguliran_i/aktif', [{
            data: 'anggota.namadepan',
            name: 'anggota.namadepan'
        }, {
            data: 'anggota.alamat',
            name: 'anggota.alamat'
        }, {
            data: 'tgl_cair',
            name: 'tgl_cair'
        }, {
            data: 'alokasi',
            name: 'alokasi'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        var tbLunas = CreateTable('#TbLunas', '/perguliran_i/lunas', [{
            data: 'anggota.namadepan',
            name: 'anggota.namadepan'
        }, {
            data: 'anggota.alamat',
            name: 'anggota.alamat'
        }, {
            data: 'tgl_cair',
            name: 'tgl_cair'
        }, {
            data: 'alokasi',
            name: 'alokasi'
        }, {
            data: 'jasa',
            name: 'jasa',
            orderable: false,
            searchable: false
        }])

        function CreateTable(tabel, url, column) {
            var table = $(tabel).DataTable({
                language: {
                    paginate: {
                        previous: "&laquo;",
                        next: "&raquo;"
                    }
                },
                processing: true,
                serverSide: true,
                ajax: url,
                columns: column,
                order: [
                    [2, 'desc']
                ]
            })

            return table
        }

        $('#TbProposal').on('click', 'tbody tr', function(e) {
            var data = tbProposal.row(this).data();

            window.location.href = '/detail_i/' + data.id
        })

        $('#TbVerified').on('click', 'tbody tr', function(e) {
            var data = tbVerified.row(this).data();

            window.location.href = '/detail_i/' + data.id
        })

        $('#TbWaiting').on('click', 'tbody tr', function(e) {
            var data = tbWaiting.row(this).data();

            window.location.href = '/detail_i/' + data.id
        })

        $('#TbAktif').on('click', 'tbody tr', function(e) {
            var data = tbAktif.row(this).data();

            window.location.href = '/detail_i/' + data.id
        })

        $('#TbLunas').on('click', 'tbody tr', function(e) {
            var data = tbLunas.row(this).data();

            window.location.href = '/lunas_i/' + data.id
        })
    </script>
@endsection
