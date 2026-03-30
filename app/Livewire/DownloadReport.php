<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class DownloadReport extends Component
{
    use WithPagination;

    #[Url()]
    public $searchDate = '';

    #[Url()]
    public $perPage = 5;
    public $currentPage = 1;

    public $loaded = false;

    public function loadInitialReports()
    {
        $this->loaded = true;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function goToPage($page)
    {
        $this->currentPage = max(1, min($page, $this->getReports()->count() / $this->perPage + 1));
    }

    /**
     * Tentukan folder laporan berdasarkan cabang aktif
     */
    private function getReportFolder(): string
    {
        $activeBranchId = session('active_branch_id');
        if ($activeBranchId) {
            return "reports/branch_{$activeBranchId}";
        }
        return 'reports/global';
    }

    /**
     * Mengambil laporan HANYA dari folder cabang aktif
     */
    private function getReports()
    {
        $folder = $this->getReportFolder();
        
        // Cek apakah folder ada, jika tidak return collection kosong
        if (!Storage::exists($folder)) {
            return collect();
        }

        $files = Storage::allFiles($folder);
    
        $reports = collect($files)->map(function ($file) {
            $basename = basename($file);
            return [
                'name' => $basename,
                'path' => $file,
                'size' => $this->formatSize(Storage::size($file)),
                'extension' => pathinfo($file, PATHINFO_EXTENSION),
                'last_modified' => Storage::lastModified($file),
                'last_download' => Carbon::createFromTimestamp(Storage::lastModified($file))->locale('id')->diffForHumans(),
                'url' => route('download.report', ['filename' => $basename, 'folder' => basename(dirname($file))]),
            ];
        });
    
        $reports = $reports->sortByDesc('last_modified');
    
        if (!empty($this->searchDate)) {
            $dateFormatted = Carbon::parse($this->searchDate)->format('d-m-Y');
            $reports = $reports->filter(fn($report) => str_contains($report['name'], $dateFormatted));
        }
    
        return $reports->values();
    }
    

    public $selectedFile = null;

    protected $listeners = [
        'deleteConfirmed' => 'deleteReport'
    ];

    public function deleteConfirmation($filename)
    {
        $this->selectedFile = $filename;
        $this->dispatch('showDeleteConfirmation', $filename);
    }

    public function deleteReport()
    {
        if (!$this->selectedFile) {
            return;
        }

        $folder = $this->getReportFolder();
        $filePath = $folder . '/' . $this->selectedFile;
        $filename = $this->selectedFile;

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
            $this->dispatch('fileDeleted', [
                'message' => "File \"$filename\" telah berhasil dihapus dari sistem.",
                'filename' => $filename,
                'error' => false
            ]);
        } else {
            $this->dispatch('fileDeleted', [
                'message' => "File \"$filename\" tidak ditemukan. Mungkin telah dihapus sebelumnya atau tidak tersedia.",
                'filename' => $filename,
                'error' => true
            ]);
        }

        $this->selectedFile = null;
        $this->resetPage();
    }

    private function formatSize($bytes)
    {
        if ($bytes <= 0) return "0 B";

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor(log($bytes, 1024));

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    public function render()
    {
        if (!$this->loaded) {
            $folder = $this->getReportFolder();
            $files = Storage::exists($folder) ? collect(Storage::allFiles($folder)) : collect();
            if (!empty($this->searchDate)) {
                $dateFormatted = Carbon::parse($this->searchDate)->format('d-m-Y');
                $files = $files->filter(fn($file) => str_contains($file, $dateFormatted));
            }
            $totalReports = $files->count();

            return view('livewire.download-report', [
                'reports' => new LengthAwarePaginator([], $totalReports, $this->perPage, $this->currentPage),
                'totalReports' => $totalReports
            ]);
        }

        $reports = $this->getReports();

        $currentPage = $this->currentPage;
        $paginatedReports = new LengthAwarePaginator(
            $reports->forPage($currentPage, $this->perPage),
            $reports->count(),
            $this->perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return view('livewire.download-report', [
            'reports' => $paginatedReports,
            'totalReports' => $reports->count()
        ]);
    }
}
