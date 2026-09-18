@php
    use App\Utils\Tanggal;
@endphp

@extends('perguliran_i.dokumen.layout.base')

@section('content')
<table width="97%" border="0" align="center" cellpadding="3" cellspacing="0">

<tr>
        <td height="70" colspan="3" class="bottom">
        <p align="center" class="style6" style="font-size: 18px; font-weight: bold;">SURAT KUASA KHUSUS</p>

        </td>
    </tr>

      <tr>
        <td height="10" colspan="3" class="style9"></td>
      </tr>
      <tr>
        <td height="10" colspan="3" class="style9">Yang bertanda tangan dan/atau membubuhkan cap jempol dibawah ini,</td>
      </tr>
      <tr>
        <td height="10" class="style9">Nama Lengkap </td>
        <td class="style27">: {{$pinkel->anggota->namadepan}}</td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>
      <tr>
        <td height="10" class="style9">Jenis Kelamin </td>
        <td class="style27">: {{$pinkel->anggota->jk}}</td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>
	   <tr>
        <td height="10" class="style9">NIK</td>
        <td class="style27">: {{$pinkel->anggota->nik}}</td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>
      <tr>
        <td height="10" class="style9">Tempat, Tanggal Lahir </td>
        <td class="style27">:  {{ $pinkel->anggota->tempat_lahir}}
                {{ Tanggal::tglLatin($pinkel->anggota->tgl_lahir) }}
            </td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>
      <tr>
        <td width="20%" height="10" class="style9">Alamat</td>
        <td width="42%" class="style27">: {{ $pinkel->anggota->d->nama_desa }}</td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>
      <tr>
        <td height="10" class="style9">Jenis Usaha</td>
        <td class="style27">: {{ is_numeric($pinkel->anggota->usaha) ? $pinkel->anggota->u->nama_usaha : $pinkel->anggota->usaha }}</td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>
	  <tr>
        <td height="10" class="style9">Nomor HP</td>
        <td class="style27">: {{ $pinkel->anggota->hp }}</td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>


      <tr>
        <td height="20" colspan="3" class="style9">Dengan ini memberikan persetujuan dan kuasa sepenuhnya kepada,</td>
      </tr>
      <tr>
        <td height="10" class="style9">Nama Lengkap </td>
        <td class="style27">:   {{ $dir->namadepan }} {{$dir->namabelakang}}</td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>
      <tr>
        <td height="10" class="style9">Jabatan </td>
        <td class="style27">: {{ $kec->sebutan_level_1 }} {{ $kec->nama_lembaga_sort }}</td>
        <td height="10" class="style9">&nbsp;</td>
      </tr>
      </table>

      <style>
    .align-justify {
        text-align: justify;
    }
</style>

<table>
    <tr>
        <td height="10" colspan="3" class="style9 align-justify">
            <p>
                Untuk itu, dengan ini saya memberikan **kuasa khusus** kepada {{ $kec->nama_lembaga_sort }} untuk melakukan tindakan yang diperlukan terhadap barang jaminan yang telah saya serahterimakan sebagaimana tercantum dalam Bukti Serah Terima Barang Jaminan, yang merupakan bagian tidak terpisahkan dari kelengkapan dokumen pencairan pinjaman.
            </p>
            <p>
                Apabila dalam pelaksanaan kewajiban pembayaran pinjaman terjadi keterlambatan atau kemacetan angsuran, maka saya memberikan kuasa kepada {{ $kec->nama_lembaga_sort }} untuk melakukan tindakan terhadap barang jaminan tersebut sesuai dengan ketentuan yang berlaku, sebagai bagian dari upaya penyelesaian kewajiban pengembalian pinjaman saya kepada {{ $kec->nama_lembaga_sort }}.
            </p>
            <p>
                Demikian Surat Persetujuan/Pernyataan sekaligus Surat Kuasa Khusus ini saya buat dengan sebenar-benarnya, dalam keadaan sadar dan tanpa adanya paksaan, tekanan, maupun pengaruh dari pihak mana pun, untuk dapat dipergunakan sebagaimana mestinya.
            </p>
        </td>
    </tr>

      <tr>
        <td height="20" colspan="2" class="style9"></td>
        <td height="20" class="style9"><div align="right"><span class="style9"><br>{{$pinkel->anggota->d->nama_desa}}, {{ Tanggal::tglLatin($pinkel->tgl_proposal) }}</span></div></td>
      </tr>
</table>

<table width="97%" border="0" align="center" cellpadding="3" cellspacing="0">

  <tr>
        <td width="10%" height="30" colspan="1" class="style26"><div align="center" class="style9">
          <p></p>
        </div></td>
		 <td class="style26"><div align="center" class="style9">
          <p>Pemberi Kuasa <br><br><br><br>
          {{ $dir->namadepan }} {{$dir->namabelakang}}
        </div></td>
        <td class="style26"><div align="center" class="style9">
          <p>Penerima Kuasa <br><br><br><br>
{{$pinkel->anggota->namadepan}}        </p>
         </p>
        </div></td>
      </tr>

</table>
@endsection
