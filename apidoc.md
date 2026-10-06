# API Documentation: Microservice Sekolah

# Base URL: https://api.bukittinggikota.go.id/sekolah

# header x-api-key: l5j4zTkz3xMV35pSqTkc9Epe800y1BoB

## 1. Get Data Siswa

**Endpoint**: GET /v1/data-siswa/{npsn}/{semester}/{nisn}
**Description**: Mengambil detail data siswa berdasarkan NPSN, Semester, dan NISN, beserta objek sekolah.

### Example Response:

`json
{
  "success": true,
  "message": "Berhasil mengambil data siswa",
  "data": {
    "peserta_didik_id": "ab207f28-ae02-4cf6-b0b2-6113b909d369",
    "nis": "13913",
    "nisn": "0117450286",
    "nm_siswa": "ERZA KSATRIA DALIMUNTE",
    "tempat_lahir": "KARANG ENDAH",
    "tanggal_lahir": "2011-10-09T00:00:00Z",
    "jenis_kelamin": "L",
    "agama": "Islam",
    "alamat_siswa": "Asrama Kodim 0304/Agam",
    "telepon_siswa": "085267054725",
    "diterima_tanggal": "2023-07-01T00:00:00Z",
    "nm_ayah": "JAMALUDDIN DALIMUNTE",
    "nm_ibu": "ATIK SUNARTI",
    "pekerjaan_ayah": "PNS/TNI/Polri",
    "pekerjaan_ibu": "Lainnya",
    "nm_wali": null,
    "pekerjaan_wali": "",
    "status_dalam_kel": null,
    "anak_ke": 2,
    "sekolah_asal": "SD NEGERI 10 SAPIRAN",
    "diterima_kelas": null,
    "alamat_ortu": null,
    "telepon_ortu": null,
    "alamat_wali": null,
    "telepon_wali": null,
    "foto_siswa": null,
    "no_ijasahnas": "121202600680233",
    "tgl_lulus": "2026-06-02T00:00:00Z",
    "no_transkrip": "400.3.11/071/SMPN-1BKT/2026",
    "rombongan_belajar_id": "d54eb3d6-a9a4-4006-a017-97c26764ba3b",
    "nm_kelas": "8G",
    "jenis_rombel": 1,
    "kurikulum_id": 10211,
    "jurusan_id": null,
    "sekolah": {
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "nama": "SMP NEGERI 1 BUKITTINGGI",
      "npsn": "10307975",
      "nss": "201086001001",
      "alamat": "Jl. Jenderal Sudirman No. 1 Bukittinggi",
      "kd_pos": "26116",
      "telepon": "075221010",
      "fax": "075221010",
      "kelurahan": "Bukit Cangang Kayu Ramang",
      "kecamatan": "Kec. Guguk Panjang",
      "kab_kota": "Kota Bukittinggi",
      "propinsi": "Prov. Sumatera Barat",
      "website": "http://smpnegeri1bukittinggi.sch.id",
      "email": "bukittinggi.smpn1bkt@gmail.com",
      "nm_kepsek": "Neldawati, S.Pd",
      "nip_kepsek": "197803042009012003",
      "niy_kepsek": null,
      "status_kepemilikan_id": 1,
      "kode_aktivasi": "1",
      "jenjang": "SMP",
      "bentuk_pendidikan_id": 6
    }
  }
}
`

## 2. Get Data Kelas

**Endpoint**: GET /v1/data-kelas/{npsn}/{semester}
**Description**: Mengambil daftar kelas (rombongan belajar) berdasarkan NPSN dan Semester dengan filter jenis_rombel = 1.

### Example Response:

`json
{
  "success": true,
  "message": "Berhasil mengambil data kelas",
  "data": [
    {
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "70ff4386-2ff5-e011-ace9-fb0fc6d13103",
      "nm_kelas": "8B",
      "tingkat_pendidikan_id": 8,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "f6d64089-b362-4df4-8fb1-a38e024fbc84",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "bbca138c-7fe3-11e3-9e44-8708186f1d5a",
      "nm_kelas": "8D",
      "tingkat_pendidikan_id": 8,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "83935868-45f3-4369-9ec3-6679219234bd",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "c64689c2-2d93-11e4-9466-e38976b92a10",
      "nm_kelas": "8E",
      "tingkat_pendidikan_id": 8,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "3563b0cf-a1ba-4195-b651-27b8e1bf148b",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "5504a59c-d601-11e5-8de5-3b66c11d646d",
      "nm_kelas": "8F",
      "tingkat_pendidikan_id": 8,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "d54eb3d6-a9a4-4006-a017-97c26764ba3b",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "ccf94692-43ea-11e5-a981-c789fc926abc",
      "nm_kelas": "8G",
      "tingkat_pendidikan_id": 8,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "96f72bf1-b9fc-4dde-8cef-c7494b84910a",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "ed421012-4209-472c-8af6-2ff584decf9c",
      "nm_kelas": "8H",
      "tingkat_pendidikan_id": 8,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "48f60a53-4d23-43a7-86c7-11f64c2c4975",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "60674086-2ff5-e011-a29a-ff9f8c4226f2",
      "nm_kelas": "9A",
      "tingkat_pendidikan_id": 9,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "66f05098-556e-49e4-8841-86e449267613",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "95151ccb-26d7-e111-9664-f99218893eff",
      "nm_kelas": "9B",
      "tingkat_pendidikan_id": 9,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "ecd6f4fc-30a9-4ec2-b085-777892d8ac4b",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "30013b86-2ff5-e011-a732-736530bce35d",
      "nm_kelas": "9C",
      "tingkat_pendidikan_id": 9,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "10e11766-377a-4411-adaf-6047188e916a",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "b7c092f4-2d97-11e4-a020-b3531f23fbb4",
      "nm_kelas": "9D",
      "tingkat_pendidikan_id": 9,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "1c7f4600-1dfd-4b94-8ec8-e7267c8b809b",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "e02a3c86-2ff5-e011-84a9-6f7e2b596ee6",
      "nm_kelas": "9E",
      "tingkat_pendidikan_id": 9,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "2ccbf0e4-89eb-45ef-b487-fb4af2e56e47",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "ec0a60e0-c9f4-e111-a49d-9d65181f4f5c",
      "nm_kelas": "9F",
      "tingkat_pendidikan_id": 9,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "7c0102aa-0cfd-41b2-97f8-46bd55a07e80",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "c0533986-2ff5-e011-8598-9931c3739f63",
      "nm_kelas": "9G",
      "tingkat_pendidikan_id": 9,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "d11b6711-62ee-49ed-87c8-38ef0a4e53a9",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "433809da-857c-11e3-b596-cb92cda41304",
      "nm_kelas": "9H",
      "tingkat_pendidikan_id": 9,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "3e56cc8f-3cc3-4514-b697-103222da99c6",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "706f388c-68f6-484f-9a2f-a9c74417b1ab",
      "nm_kelas": "7G",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "ed4b8a76-6e50-41cf-9ac6-97d575bf2f09",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "22fd1cd3-3218-e211-9b08-6f8fff070b03",
      "nm_kelas": "7I",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "e3590c31-fc1d-41bf-bd21-b3962049b0be",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "4f4b9c2f-c05e-e211-81d4-7ba0094b8e14",
      "nm_kelas": "8C",
      "tingkat_pendidikan_id": 8,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "b723e078-8ebe-4251-9487-b28ca8efc3f7",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "48c120e3-e696-4364-b6e1-1f1f95aef2bf",
      "nm_kelas": "7A",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "2b26bb6e-c244-4a10-9630-7aaee3f0509b",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "bb2f693c-a7ea-e111-a34a-5753e6185ef7",
      "nm_kelas": "7B",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "c3581d97-d81b-4489-ab6b-02e48591536e",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "7a2e402d-c5f0-e111-ae4d-49a3a2b8e31c",
      "nm_kelas": "7C",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "44588a68-dd7c-46ff-9b82-612475497429",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "640bbdc5-f2ba-456c-9c11-7a0e484ab17b",
      "nm_kelas": "7D",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "a72f68ad-24a2-4268-9e16-f9d05ba8f7b7",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "13bdedeb-2d18-457a-9bd8-aeb157b49a94",
      "nm_kelas": "7E",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "8dafd2f2-2c93-4a50-af7c-e5c5dc1dea7b",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "36efbdb4-856c-4bf7-858e-02c42b82396e",
      "nm_kelas": "7F",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "00eebc41-413a-491c-b45d-195fa2ef04cf",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "89848b8e-4149-4fc2-a681-6d1ef7540979",
      "nm_kelas": "7H",
      "tingkat_pendidikan_id": 7,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    },
    {
      "rombongan_belajar_id": "f6f43cf3-c18b-4e2a-90b7-7335dcba305d",
      "sekolah_id": "c0fd8779-2ff5-e011-b8f3-f34a5973b111",
      "semester_id": "20242",
      "jurusan_id": null,
      "ptk_id": "0a690042-0b05-46bc-b63a-3966f40799d2",
      "nm_kelas": "8A",
      "tingkat_pendidikan_id": 8,
      "jenis_rombel": 1,
      "nama_jurusan_sp": null,
      "jurusan_sp_id": null,
      "kurikulum_id": 10211,
      "program": null,
      "konsentrasi": null
    }
  ]
}
`

## 3. Get Data Sekolah

**Endpoint**: GET /v1/data-sekolah
**Description**: Mengambil daftar seluruh sekolah (Nama Sekolah dan NPSN) dari konfigurasi config.json.

### Example Response:

`json
{
  "success": true,
  "message": "Berhasil mengambil data daftar sekolah",
  "data": [
    {
      "nama_sekolah": "SD JAMIYYATUL HUJJAJ BUKITTINGGI",
      "npsn": "10300009"
    },
    {
      "nama_sekolah": "SD NEGERI 11 CAMPAGO GUGUK BULEK",
      "npsn": "10300042"
    },
    {
      "nama_sekolah": "SD NEGERI 19 ATTS",
      "npsn": "10300055"
    },
    {
      "nama_sekolah": "SDIT SYAHIRAL ILMI",
      "npsn": "10300065"
    },
    {
      "nama_sekolah": "SD NEGERI 05 TAROK DIPO",
      "npsn": "10300025"
    },
    {
      "nama_sekolah": "SD FRANSISKUS",
      "npsn": "10300001"
    },
    {
      "nama_sekolah": "SD ISLAM TERPADU MASYITHAH",
      "npsn": "10300007"
    },
    {
      "nama_sekolah": "SD NEGERI 02 PERCONTOHAN",
      "npsn": "10300016"
    },
    {
      "nama_sekolah": "SMP NEGERI 1 BUKITTINGGI",
      "npsn": "10307975"
    },
    {
      "nama_sekolah": "SMP NEGERI 2 BUKITTINGGI",
      "npsn": "10300074"
    },
    {
      "nama_sekolah": "SMP NEGERI 6 BUKITTINGGI",
      "npsn": "10300078"
    },
    {
      "nama_sekolah": "SD NEGERI 10 AUR TAJUNGKANG TENGAH SAWAH",
      "npsn": "10300037"
    },
    {
      "nama_sekolah": "SD NEGERI 18 CAMPAGO GUGUK BULEK",
      "npsn": "10300053"
    },
    {
      "nama_sekolah": "SD SURYA KIDS",
      "npsn": "10300057"
    },
    {
      "nama_sekolah": "SMP IT ALKAUTSAR ISLAMIC SCHOOL",
      "npsn": "10300070"
    },
    {
      "nama_sekolah": "SMP NEGERI 4 BUKITTINGGI",
      "npsn": "10300076"
    },
    {
      "nama_sekolah": "SD NEGERI 17 MANGGIS GANTING",
      "npsn": "10300051"
    },
    {
      "nama_sekolah": "SD NEGERI 07 BELAKANG BALOK",
      "npsn": "10300029"
    },
    {
      "nama_sekolah": "SD NEGERI 09 BELAKANG BALOK",
      "npsn": "10300035"
    },
    {
      "nama_sekolah": "SD NEGERI 10 PUHUN PINTU KABUN",
      "npsn": "10300038"
    },
    {
      "nama_sekolah": "SDIT AL AZHAR DARUL JANNAH",
      "npsn": "10300061"
    },
    {
      "nama_sekolah": "SMP PAUS BIRU",
      "npsn": "10300081"
    },
    {
      "nama_sekolah": "SMPIT CAHAYA HATI BUKITTINGGI",
      "npsn": "10300085"
    },
    {
      "nama_sekolah": "SD ISLAM AL AZHAR 67 BUKITTINGGI",
      "npsn": "10300002"
    },
    {
      "nama_sekolah": "SD NEGERI 01 BENTENG PASAR ATAS",
      "npsn": "10300011"
    },
    {
      "nama_sekolah": "SD NEGERI 02 CAMPAGO GUGUK BULEK",
      "npsn": "10300015"
    },
    {
      "nama_sekolah": "SD NEGERI 04 BIRUGO",
      "npsn": "10300020"
    },
    {
      "nama_sekolah": "SD NEGERI 04 BUKIT APIT PUHUN",
      "npsn": "10300021"
    },
    {
      "nama_sekolah": "SD NEGERI 07 KUBU GULAI BANCAH",
      "npsn": "10300030"
    },
    {
      "nama_sekolah": "SD NEGERI 08 TAROK DIPO",
      "npsn": "10300034"
    },
    {
      "nama_sekolah": "SD TRISULA PERWARI",
      "npsn": "10300058"
    },
    {
      "nama_sekolah": "SD NEGERI 02 AUR KUNING",
      "npsn": "10300014"
    },
    {
      "nama_sekolah": "SD NEGERI 08 CAMPAGO IPUH",
      "npsn": "10300032"
    },
    {
      "nama_sekolah": "SD NEGERI 13 KUBU GULAI BANCAH",
      "npsn": "10300046"
    },
    {
      "nama_sekolah": "SDI SJECH M. DJAMIL DJAMBEK",
      "npsn": "10300059"
    },
    {
      "nama_sekolah": "SMP ISLAM EXCELLENT PLUS BUKITTINGGI",
      "npsn": "10300069"
    },
    {
      "nama_sekolah": "SMP SURYA KIDS",
      "npsn": "10300084"
    },
    {
      "nama_sekolah": "SD NEGERI 06 AUR TAJUNGKANG TENGAH SAWAH",
      "npsn": "10300026"
    },
    {
      "nama_sekolah": "SD NEGERI 16 TAROK DIPO",
      "npsn": "10300050"
    },
    {
      "nama_sekolah": "SD NEGERI 03 PULAI ANAK AIR",
      "npsn": "10300019"
    },
    {
      "nama_sekolah": "SD NEGERI 12 PUHUN PINTU KABUN",
      "npsn": "10300044"
    },
    {
      "nama_sekolah": "SMP NEGERI 5 BUKITTINGGI",
      "npsn": "10300077"
    },
    {
      "nama_sekolah": "SD ISLAM EXCELLENT PLUS",
      "npsn": "10300006"
    },
    {
      "nama_sekolah": "SD NEGERI 11 BUKIT APIT PUHUN",
      "npsn": "10300041"
    },
    {
      "nama_sekolah": "SD NEGERI 12 BUKIT CANGANG",
      "npsn": "10300043"
    },
    {
      "nama_sekolah": "SD NEGERI 13 BUKIT APIT PUHUN",
      "npsn": "10300045"
    },
    {
      "nama_sekolah": "SD NEGERI 14 ATTS",
      "npsn": "10300047"
    },
    {
      "nama_sekolah": "SD NEGERI 15 PULAI ANAK AIR",
      "npsn": "10300048"
    },
    {
      "nama_sekolah": "SD NEGERI 16 CAMPAGO IPUH",
      "npsn": "10300049"
    },
    {
      "nama_sekolah": "SD ISLAM CENDEKIA",
      "npsn": "10300005"
    },
    {
      "nama_sekolah": "SD NEGERI 01 CAMPAGO IPUH",
      "npsn": "10300012"
    },
    {
      "nama_sekolah": "SD NEGERI 01 LADANG CAKIAH",
      "npsn": "10300013"
    },
    {
      "nama_sekolah": "SD NEGERI 04 GAREGEH",
      "npsn": "10300022"
    },
    {
      "nama_sekolah": "SD NEGERI 06 PARIT ANTANG",
      "npsn": "10300027"
    },
    {
      "nama_sekolah": "SD NEGERI 17 PAKAN KURAI",
      "npsn": "10300052"
    },
    {
      "nama_sekolah": "SDIT ADZKIA BUKITTINGGI",
      "npsn": "10300060"
    },
    {
      "nama_sekolah": "SDIT BRILLIANT SCHOOL BUKITTINGGI",
      "npsn": "10300062"
    },
    {
      "nama_sekolah": "SD LEBAH PEMBELAJAR",
      "npsn": "10300010"
    },
    {
      "nama_sekolah": "SD NEGERI 08 KUBU TANJUNG",
      "npsn": "10300033"
    },
    {
      "nama_sekolah": "SD NEGERI 18 TAROK DIPO",
      "npsn": "10300054"
    },
    {
      "nama_sekolah": "SDIT INSAN KAMIL",
      "npsn": "10300064"
    },
    {
      "nama_sekolah": "SMP JAMIYYATUL HUJJAJ",
      "npsn": "10300072"
    },
    {
      "nama_sekolah": "SMP NEGERI 7 BUKITTINGGI",
      "npsn": "10300079"
    },
    {
      "nama_sekolah": "SMP NEGERI 8 BUKITTINGGI",
      "npsn": "10300080"
    },
    {
      "nama_sekolah": "SMP SEKOLAH ALAM BUKITTINGGI",
      "npsn": "10300083"
    },
    {
      "nama_sekolah": "SD NEGERI 11 AUR KUNING",
      "npsn": "10300040"
    },
    {
      "nama_sekolah": "SD SEKOLAH ALAM BUKITTINGGI",
      "npsn": "10300056"
    },
    {
      "nama_sekolah": "SD ISLAM AL FALAH",
      "npsn": "10300003"
    },
    {
      "nama_sekolah": "SD NEGERI 05 PERCOBAAN PUHUN PINTU KABUN",
      "npsn": "10300024"
    },
    {
      "nama_sekolah": "SD NEGERI 07 TELADAN BUKITTINGGI",
      "npsn": "10300031"
    },
    {
      "nama_sekolah": "SD NEGERI 09 MANGGIS GANTING",
      "npsn": "10300036"
    },
    {
      "nama_sekolah": "SD NEGERI 10 SAPIRAN",
      "npsn": "10300039"
    },
    {
      "nama_sekolah": "SMP ISLAM AL AZHAR 39",
      "npsn": "10300067"
    },
    {
      "nama_sekolah": "SD NEGERI 05 BIRUGO BUKITTINGGI",
      "npsn": "10300023"
    },
    {
      "nama_sekolah": "SDIT CAHAYA HATI",
      "npsn": "10300063"
    },
    {
      "nama_sekolah": "SMP IT SJECH M. DJAMIL DJAMBEK",
      "npsn": "10300071"
    },
    {
      "nama_sekolah": "SD IT ALKAUTSAR ISLAMIC SCHOOL",
      "npsn": "10300008"
    },
    {
      "nama_sekolah": "SD NEGERI 03 PAKAN LABUAH",
      "npsn": "10300018"
    },
    {
      "nama_sekolah": "SDS IT ULUL ALBAB BUKITTINGGI",
      "npsn": "10300066"
    },
    {
      "nama_sekolah": "SMP NEGERI 3 BUKITTINGGI",
      "npsn": "10300075"
    },
    {
      "nama_sekolah": "SMPS XAVERIUS",
      "npsn": "10300086"
    },
    {
      "nama_sekolah": "SD ISLAM AL ISHLAH BUKITTINGGI",
      "npsn": "10300004"
    },
    {
      "nama_sekolah": "SD NEGERI 03 PAKAN KURAI",
      "npsn": "10300017"
    },
    {
      "nama_sekolah": "SD NEGERI 06 PULAI ANAK AIR",
      "npsn": "10300028"
    },
    {
      "nama_sekolah": "SMP ISLAM AL ISHLAH",
      "npsn": "10300068"
    },
    {
      "nama_sekolah": "SMP PSM BUKITTINGGI",
      "npsn": "10300082"
    }
  ]
}
`

## 4. Get Data Anggota Kelas

**Endpoint**: `GET /v1/data-kelas/{npsn}/{semester}/{rombongan_belajar_id}`
**Description**: Mengambil detail daftar siswa dalam satu rombongan belajar (kelas), termasuk informasi pribadi siswa seperti NIS, NISN, Nama, dan Jenis Kelamin.

### Example Response:

```json
{
  "success": true,
  "message": "Berhasil mengambil data anggota kelas",
  "data": [
    {
      "anggota_rombel_id": "7fc02d99-9353-4e87-9c0a-ea59c4c0213d",
      "peserta_didik_id": "6598d004-41e1-4174-b115-3e1739070ed1",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13955",
      "nisn": "0116375203",
      "nm_siswa": "MHD. NABIL ADLI",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "a5ae15d3-5a09-4a6b-a463-2a73c09aedac",
      "peserta_didik_id": "e757610c-5828-4023-831a-dae55db88015",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13999",
      "nisn": "0118290296",
      "nm_siswa": "ZAKY ABRAR",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "984c860b-1de8-40ae-a132-6d46f03412de",
      "peserta_didik_id": "d898d452-48b5-4b50-b032-e5bf1d66803b",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14028",
      "nisn": "0114375297",
      "nm_siswa": "ALISHA I'ANATUZAHRO",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "ac5bedb0-4b18-44fa-bcfa-ffe4ca1030a9",
      "peserta_didik_id": "231d28c5-660d-4415-9aef-71f1fd080cfb",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14039",
      "nisn": "0112300944",
      "nm_siswa": "MUHAMMAD ZAIDAN RAMADHAN",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "f79b55e3-216f-4a1a-8122-bab368f7f75f",
      "peserta_didik_id": "63c6d7a6-e7e5-4e9e-a414-cc1560035120",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14054",
      "nisn": "0118803316",
      "nm_siswa": "AHZA NABIL HANANIA",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "0933e394-7139-4f35-8a36-b9d9f324a31a",
      "peserta_didik_id": "1d68ad11-d296-493d-bbc3-bf419f9977d5",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13912",
      "nisn": "0103037959",
      "nm_siswa": "DZAKI FAZLI ADAM",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "ae4a76b1-7cc9-4b73-8a30-509a7622ce06",
      "peserta_didik_id": "26a574eb-5504-4060-9262-15a17f7f2d34",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14091",
      "nisn": "0104333481",
      "nm_siswa": "FARID ALFATTAH",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "83a23ea8-93dc-44e5-bfa4-0445d62e4060",
      "peserta_didik_id": "4c75a46d-74c5-4609-a91b-7b6047251f99",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13892",
      "nisn": "0105109992",
      "nm_siswa": "JIBRAN GAZZALA MAJID",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "ee5525a9-67e8-4521-b2da-2a4ae64c1821",
      "peserta_didik_id": "eb300019-e828-437c-b7e6-f27a1462a791",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13889",
      "nisn": "0107037818",
      "nm_siswa": "FARRAS ADISTYO",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "aa910b32-16f7-46a5-9ae2-06ff47cd3e9b",
      "peserta_didik_id": "9c710b82-0b85-492e-bb65-b10f6e50b882",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14015",
      "nisn": "0112911502",
      "nm_siswa": "MUHAMMAD DIGO MAULANA",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "a8d4356a-5bd7-4d1f-ab31-0a2c1c4a7811",
      "peserta_didik_id": "5470f928-5251-4037-b0ab-b610f07aa3b2",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13921",
      "nisn": "0102314600",
      "nm_siswa": "LISHA MAULINA SYAFITRI",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "8913d155-20e7-4617-adc8-31ae5ecd118b",
      "peserta_didik_id": "2ab452e5-222c-4923-b3cc-df64402adb60",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14126",
      "nisn": "0115492386",
      "nm_siswa": "FEBY ELFINA SYAKIRA",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "3e2f5735-7c97-4362-936a-ea4c21fd0c9d",
      "peserta_didik_id": "c17f5018-e0f4-4ed7-8f0c-d7b6d4def286",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13998",
      "nisn": "0118327940",
      "nm_siswa": "YASMINE ALYA ZEVKA",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "1d99b2de-3480-4cb4-bac6-624da12fcb52",
      "peserta_didik_id": "e81f8208-0ab6-486b-aa1c-b61e15905e4d",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14016",
      "nisn": "0111978863",
      "nm_siswa": "MUHAMMAD HAFIZH",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "4c0741d0-db7f-4976-bcb2-de42110d165a",
      "peserta_didik_id": "aedefaed-f95a-43b4-b41d-06a4ac5ac6f8",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13891",
      "nisn": "0108325598",
      "nm_siswa": "HAIKAL HADI",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "ea258d09-70c5-4ee0-97ff-41943e31c911",
      "peserta_didik_id": "23398f55-4457-4851-a2eb-835c27ba7aa1",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14100",
      "nisn": "0104576261",
      "nm_siswa": "NAILA PUTRI RAMADHANI",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "b1a3df34-44da-4c94-a9a2-0d7ba25c0fa8",
      "peserta_didik_id": "2b134143-0897-465b-86ed-a2278f7a709f",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13940",
      "nisn": "0112786615",
      "nm_siswa": "ARYA VINCENT NALAYA",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "bd84e3c6-056d-45e4-aefb-a55a68db8695",
      "peserta_didik_id": "9f773aaf-6caf-4c52-b248-e60a303b22b8",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13968",
      "nisn": "0111074701",
      "nm_siswa": "TSAMARA ALTIFA HARAPAN LUBIS",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "ee5f494b-64bd-4310-8614-c8c514d60a71",
      "peserta_didik_id": "32609120-63bc-48c5-8a67-82b3e7090724",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14093",
      "nisn": "0103533476",
      "nm_siswa": "HAZZEL SACHIO DEVARA",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "6cab921f-3b7c-430f-bb07-bf27ea4712c5",
      "peserta_didik_id": "c63b71c3-a1e4-4edb-bd8a-572a77344d5a",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13888",
      "nisn": "0102190794",
      "nm_siswa": "EQUINZA SHAFANA",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "ecb93be9-d758-4975-838b-04002b98c9f5",
      "peserta_didik_id": "93bc112f-ce78-4c8d-8fec-e7aa7c8906b7",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13975",
      "nisn": "0116852898",
      "nm_siswa": "AYU LISTIYANI",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "7a28fab0-68a3-49ee-956a-1593f0eb5fb9",
      "peserta_didik_id": "b89b0eff-8a94-43ec-946b-527a76f6787f",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13899",
      "nisn": "0112808153",
      "nm_siswa": "MUHAMMAD RIDHO",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "968945b4-5eaf-475b-81ce-133bc7324f8a",
      "peserta_didik_id": "eaa4523f-5caa-4b59-ad91-adf3569ef2d6",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13993",
      "nisn": "0111983092",
      "nm_siswa": "RAJASWA BRAJA ILHAM",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "2d9b70bd-3e99-4e5b-abc4-839348031442",
      "peserta_didik_id": "4254fa64-6fbc-4a13-b24f-080c00151e21",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13990",
      "nisn": "0115182155",
      "nm_siswa": "NAYLA ZHARIFA",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "5f29e263-50d5-4074-b2a0-aa047a412ce9",
      "peserta_didik_id": "c4e3e430-a027-4bd5-9cfa-90b3048c7663",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13986",
      "nisn": "0119066487",
      "nm_siswa": "MUHAMMAD IRFAN FATHONI",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "969dfcfd-a20c-4f7f-8e25-4b555fe6a754",
      "peserta_didik_id": "301f3666-cebe-457c-b4c1-04bf40253329",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14022",
      "nisn": "0111261458",
      "nm_siswa": "PUJA ASY SYIFA",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "684cc88b-bb09-40f0-8acd-3e0ff439f777",
      "peserta_didik_id": "bc88baf0-6ed9-43bd-9c08-bd9208f9e5ce",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14095",
      "nisn": "0101020142",
      "nm_siswa": "LATHIFA ABIDAH HAZIQA",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "7187c1be-5423-40c7-bba9-89e39c650af7",
      "peserta_didik_id": "b1f7040e-2d87-4d1e-b390-954e806d7599",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13909",
      "nisn": "0112137785",
      "nm_siswa": "AZZURA NABILA KHAIRA",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "b00b06f6-6efc-4615-bbd0-8294b58525cd",
      "peserta_didik_id": "16de4bd9-16cd-4281-853a-80f1fd88814e",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14416",
      "nisn": "0117535821",
      "nm_siswa": "CHAYRA ANNISA",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "a6a17740-4457-4f43-8888-a0fefa31b4bc",
      "peserta_didik_id": "6a7d8547-b74b-4c5f-98f2-02b43c410004",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13964",
      "nisn": "0105151139",
      "nm_siswa": "RAISYA KHOIRI RAMADHANI",
      "jenis_kelamin": "P"
    },
    {
      "anggota_rombel_id": "a30e6d6b-87a3-4b07-959b-e68ccbec402f",
      "peserta_didik_id": "f943058a-0868-4056-91fb-31e015608af1",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13925",
      "nisn": "0105877869",
      "nm_siswa": "MUHAMMAD RAFA ASYRAF",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "b74622e8-7b2a-4c36-bff9-aafa0da8ad4f",
      "peserta_didik_id": "747e0ac6-ff94-4859-adde-e50bf5edc0d0",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "13924",
      "nisn": "0114488038",
      "nm_siswa": "MUHAMMAD IDRUS ADAM",
      "jenis_kelamin": "L"
    },
    {
      "anggota_rombel_id": "2fb83a55-4491-4cfc-aab6-61fc08936622",
      "peserta_didik_id": "9055e95a-44ea-48e6-9e04-0822f4b7a53a",
      "rombongan_belajar_id": "979828bf-f719-4af6-b0f5-9982e916affa",
      "semester_id": "20242",
      "nis": "14070",
      "nisn": "0104760520",
      "nm_siswa": "NAUFAL KURNIA ADAM",
      "jenis_kelamin": "L"
    }
  ]
}
```

## 5. Health Check

**Endpoint**: `GET /v1/health-check`
**Description**: Endpoint untuk memantau status kesehatan (liveness) dari microservice, sering digunakan oleh load balancer atau orchestrator (seperti Docker/Kubernetes).

### Example Response:

```json
{
  "success": true,
  "message": "Service is healthy",
  "data": null
}
```

## 6. Rekap Data Siswa Lintas Sekolah

**Endpoint**: `GET /v1/data-siswa/rekap/all/{semester}`
**Description**: Mengambil data agregasi (rekapitulasi) jumlah seluruh siswa, sebaran jenis kelamin, dan jumlah siswa per kelas secara paralel (concurrent) untuk _semua_ sekolah yang terdaftar pada konfigurasi. Data difilter berdasarkan semester dan jenis rombongan belajar utama (`jenis_rombel = 1`).

### Example Response:

```json
{
  "success": true,
  "message": "Berhasil mengambil rekap data siswa per sekolah",
  "data": [
    {
      "npsn": "10307975",
      "nama_sekolah": "SMP NEGERI 1 BUKITTINGGI",
      "status_sukses": true,
      "total_siswa": 739,
      "rekap_jenis_kelamin": {
        "L": 395,
        "P": 344
      },
      "rekap_kelas": {
        "7A": 32,
        "7B": 31,
        "7C": 30,
        "7D": 32,
        "7E": 32,
        "7F": 32,
        "7G": 32,
        "8A": 29,
        "8B": 29,
        "8C": 29,
        "8D": 29,
        "8E": 29,
        "8F": 28,
        "8G": 28,
        "8H": 28,
        "8I": 29,
        "9A": 32,
        "9B": 32,
        "9C": 32,
        "9D": 33,
        "9E": 32,
        "9F": 33,
        "9G": 33,
        "9H": 33
      }
    },
    {
        "npsn": "10300035",
        "nama_sekolah": "SD NEGERI 09 BELAKANG BALOK",
        "status_sukses": false,
        "error_message": "Query error: dial tcp 127.0.0.1:5432: connectex: No connection could be made because the target machine actively refused it.",
        "total_siswa": 0,
        "rekap_jenis_kelamin": {},
        "rekap_kelas": {}
    },
    ...
  ]
}
```

## 7. Rekap Data Siswa Per Sekolah (Satu Sekolah)

**Endpoint**: `GET /v1/data-siswa/rekap/{npsn}/{semester}`
**Description**: Mengambil data agregasi (rekapitulasi) jumlah siswa, sebaran jenis kelamin, dan jumlah siswa per kelas secara spesifik untuk **satu sekolah** berdasarkan `NPSN`. Data difilter berdasarkan semester dan jenis rombongan belajar utama (`jenis_rombel = 1`).

### Example Response:

```json
{
  "success": true,
  "message": "Berhasil mengambil rekap data siswa",
  "data": {
    "npsn": "10307975",
    "nama_sekolah": "SMP NEGERI 1 BUKITTINGGI",
    "status_sukses": true,
    "total_siswa": 739,
    "rekap_jenis_kelamin": {
      "L": 395,
      "P": 344
    },
    "rekap_kelas": {
      "7A": 32,
      "7B": 31,
      "7C": 30,
      "7D": 32,
      "8A": 29
    }
  }
}
```
