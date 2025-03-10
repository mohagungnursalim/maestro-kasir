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
    use WithPagination; // Gunakan fitur pagination Livewire

    // Search
    #[Url()]
    public $searchDate = '';

    #[Url()]
    public $perPage = 5; // Jumlah file per halaman
    public $currentPage = 1; 


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

    private function getReports()
    {
        $files = Storage::allFiles('reports');
    
        $reports = collect($files)->map(function ($file) {
            return [
                'name' => basename($file),
                'path' => $file,
                'size' => $this->formatSize(Storage::size($file)),
                'extension' => pathinfo($file, PATHINFO_EXTENSION),
                'last_modified' => Storage::lastModified($file), // Pakai timestamp asli
                'last_download' => Carbon::createFromTimestamp(Storage::lastModified($file))->locale('id')->diffForHumans(),
                'url' => route('download.report', ['filename' => basename($file)]),
            ];
        });
    
        // **Sorting berdasarkan last_modified DESC (terbaru di atas)**
        $reports = $reports->sortByDesc('last_modified');
    
        // 🔍 Filter berdasarkan tanggal dalam nama file
        if (!empty($this->searchDate)) {
            $dateFormatted = Carbon::parse($this->searchDate)->format('d-m-Y');
            $reports = $reports->filter(fn($report) => str_contains($report['name'], $dateFormatted));
        }
    
        return $reports->values(); // Reset index array setelah sort
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

    $filePath = 'reports/' . $this->selectedFile;
    $filename = $this->selectedFile; // Simpan nama file sebelum direset

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

    $this->selectedFile = null; // Reset setelah dikirim
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
        $reports = $this->getReports();

        // 🛠️ Buat paginasi manual dengan `LengthAwarePaginator`
        $currentPage = $this->currentPage; // Livewire butuh variabel currentPage
        $paginatedReports = new LengthAwarePaginator(
            $reports->forPage($currentPage, $this->perPage),
            $reports->count(),
            $this->perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return view('livewire.download-report', [
            'reports' => $paginatedReports
        ]);
    }
}
