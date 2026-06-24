<table>
    <thead>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
            <th>5</th>
            <th>6</th>
            <th>7</th>
            <th>8</th>
            <th>9</th>
            <th>10</th>
            <th>11</th>
            <th>12</th>
            <th>13</th>
            <th>14</th>
            <th>15</th>
            <th>16</th>
            <th>17</th>
            <th>18</th>
            <th>19</th>
            <th>20</th>
            <th>21</th>
            <th>22</th>
            <th>23</th>
            <th>24</th>
            <th>25</th>
            <th>26</th>
            <th>27</th>
            <th>28</th>
            <th>29</th>
            <th>30</th>
            <th>31</th>
            <th>32</th>
            <th>33</th>
            <th>34</th>
            <th>35</th>
            <th>36</th>
        </tr>
        <tr>
            <th>No</th>
            <th>NIK</th>
            <th>Name</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th>Kewarganegaraan</th>
            <th>Tipe Identitas</th>
            <th>No. Identitas</th>
            <th>Gender</th>
            <th>Golongan Darah</th>
            <th>Agama</th>
            <th>Tinggi Badan</th>
            <th>Berat Badan</th>
            <th>Riwayat Pendidikan Terakhir</th>
            <th>Perusahaan</th>
            <th>Lokasi Kerja</th>
            <th>Departemen</th>
            <th>Jabatan</th>
            <th>Golongan</th>
            <th>Tanggal Mulai Kerja</th>
            <th>Jenis Pegawai</th>
            <th>Email Pribadi</th>
            <th>Email Kantor</th>
            <th>Telp</th>
            <th>Alamat</th>
            <th>NPWP</th>
            <th>Alamat NPWP</th>
            <th>Jamsostek</th>
            <th>Status Perkawinan</th>
            <th>Bank</th>
            <th>Cabang Bank</th>
            <th>No. Rekening</th>
            <th>Atas Nama </th>
            <th>Nama Kontak Darurat</th>
            <th>Hubungan Kontak Darurat</th>
            <th>Telp Kontak Darurat</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    @php $no = 1 @endphp
    @foreach($employee as $val)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $val->nik }}</td>
            <td>{{ $val->name }}</td>
            <td>{{ $val->place_of_birth }}</td>
            <td>{{ date('d/m/Y',strtotime( $val->date_of_birth)) }}</td>
            <td>{{ $val->kewarganegaraan }}</td>
            <td>{{ $val->identity }}</td>
            <td>{{ $val->id_no }}</td>
            <td>{{ $val->gender }}</td>
            <td>{{ $val->blood_id }}</td>
            <td>{{ $val->religion }}</td>
            <td>{{ $val->height }}</td>
            <td>{{ $val->weight }}</td>
            <td>{{ $val->riwayat_pendidikan }}</td>
            <td>{{ $val->company }}</td>
            <td>{{ $val->location }}</td>
            <td>{{ $val->department }}</td>
            <td>{{ $val->position }}</td>
            <td>{{ $val->group }}</td>
            <td>{{ date('d/m/Y',strtotime( $val->start_date)) }}</td>
            <td>{{ $val->job_status }}</td>
            <td>{{ $val->email_personal }}</td>
            <td>{{ $val->email }}</td>
            <td>{{ $val->telp }}</td>
            <td>{{ $val->address }}, Rt.{{ $val->rt }} Rw.{{ $val->address }}, {{ $val->village }} Kec. {{ $val->district }} Kab. {{ $val->regency }}, {{ $val->province }}</td>
            <td>{{ $val->npwp }}</td>
            <td>{{ $val->npwp_address }}</td>
            <td>{{ $val->jamsostek }}</td>
            <td>{{ $val->marital_status }}</td>
            <td>{{ $val->bank }}</td>
            <td>{{ $val->bank_branch }}</td>
            <td>{{ $val->bank_account_number }}</td>
            <td>{{ $val->bank_account_name }}</td>
            <td>{{ $val->emergency_contact_name }}</td>
            <td>{{ $val->emergency_contact_rel }}</td>
            <td>{{ $val->emergency_contact_phone }}</td>
            <td>{{ $val->status }}</td>

        </tr>
    @endforeach
    </tbody>
</table>