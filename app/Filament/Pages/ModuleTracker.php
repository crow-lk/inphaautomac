<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action; // Import the Action class
use Illuminate\Support\Facades\Gate;

class ModuleTracker extends Page implements Tables\Contracts\HasTable
{
    public static function canAccess(): bool
    {
        return Gate::allows('page_ModuleTracker');
    }
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Inpha BMS';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.module-tracker';

    use Tables\Concerns\InteractsWithTable;

    public ?string $gradeFilter = null; // Add a property to hold the selected grade filter

    protected function getTableQuery(): Builder
    {
        $query = Module::whereIn('id', function ($query) {
            $query->select('id')
                ->from('modules as m1')
                ->whereNotIn('battery_pack_id', function ($subQuery) {
                    $subQuery->select('id')
                        ->from('battery_packs')
                        ->where('name', 'LIKE', 'CINU%'); // Exclude battery packs with names starting with 'CINU%'
                })
                ->where('m1.created_at', function ($subQuery) {
                    $subQuery->selectRaw('MAX(m2.created_at)')
                        ->from('modules as m2')
                        ->whereColumn('m1.serial_number', 'm2.serial_number')
                        ->groupBy('m2.serial_number');
                });
        });

        // Apply the grade filter if set
        if ($this->gradeFilter) {
            switch ($this->gradeFilter) {
                case 'A':
                    $query->whereBetween('capacitance', [4000, 6000]);
                    break;
                case 'B':
                    $query->whereBetween('capacitance', [3000, 4000]);
                    break;
                case 'C':
                    $query->whereBetween('capacitance', [2000, 3000]);
                    break;
                case 'D':
                    $query->whereBetween('capacitance', [1000, 2000]);
                    break;
                case 'E':
                    $query->where('capacitance', '<', 1000);
                    break;
            }
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('serial_number')->label('Serial Number'),
            Tables\Columns\TextColumn::make('ir_value')->label('IR Value'),
            Tables\Columns\TextColumn::make('capacitance')->label('Capacitance'),
            // Add other columns as needed
        ];
    }

    protected function getTableActions(): array
    {
        return [
            // Define any actions you want to include, like edit or delete
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Grade A')
                ->label('Grade A')
                ->action(fn () => $this->gradeFilter = 'A')
                ->color($this->gradeFilter === 'A' ? 'success' : 'gray'), // Change color based on filter state
            Action::make('Grade B')
                ->label('Grade B')
                ->action(fn () => $this->gradeFilter = 'B')
                ->color($this->gradeFilter === 'B' ? 'primary' : 'gray'), // Change color based on filter state
            Action::make('Grade C')
                ->label('Grade C')
                ->action(fn () => $this->gradeFilter = 'C')
                ->color($this->gradeFilter === 'C' ? 'info' : 'gray'), // Change color based on filter state
            Action::make('Grade D')
                ->label('Grade D')
                ->action(fn () => $this->gradeFilter = 'D')
                ->color($this->gradeFilter === 'D' ? 'warning' : 'gray'), // Change color based on filter state
            Action::make('Grade E')
                ->label('Grade E')
                ->action(fn () => $this->gradeFilter = 'E')
                ->color($this->gradeFilter === 'E' ? 'danger' : 'gray'), // Change color based on filter state
            Action::make('Clear Filter')
                ->label('Clear Filter')
                ->action(fn () => $this->gradeFilter = null)
                ->color($this->gradeFilter === null ? 'gray' : 'gray'), // Keep it gray
        ];
    }
}
