<?php

namespace App\Exports;

use App\Models\Household;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HouseholdsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    /**
     * Query data untuk export
     */
    public function query()
    {
        if ($this->query) {
            return $this->query;
        }

        return Household::query()->with(['rt', 'residents']);
    }

    /**
     * Mapping data untuk tiap row
     */
    public function map($household): array
    {
        // Cari kepala keluarga dari residents
        $headOfHousehold = $household->residents
            ->first(fn ($resident) => $resident->relationship === 'Kepala Keluarga');

        return [
            $household->family_card_number,
            $household->rt->number ?? '-',
            $household->rt->name ?? '-',
            $headOfHousehold->name ?? $household->head_name,
            $headOfHousehold->nik ?? $household->head_nik,
            $household->head_gender === 'male' ? 'Laki-laki' : 'Perempuan',
            $household->address,
            $household->residents->count(),
            $household->status === 'aktif' ? 'Aktif' : ($household->status === 'non-aktif' ? 'Non-Aktif' : 'Expired'),
            $household->status_effective_date ? $household->status_effective_date->format('d M Y') : '-',
            $household->issued_at ? $household->issued_at->format('d M Y') : '-',
        ];
    }

    /**
     * Header kolom
     */
    public function headings(): array
    {
        return [
            'No. KK',
            'RT',
            'Nama RT',
            'Kepala Keluarga',
            'NIK',
            'Jenis Kelamin',
            'Alamat',
            'Jumlah Anggota',
            'Status',
            'Tgl Efektif Status',
            'Tanggal Terbit',
        ];
    }

    /**
     * Styling untuk header
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
