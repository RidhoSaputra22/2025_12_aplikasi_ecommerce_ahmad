<?php

namespace App\Filament\Pages;

use App\Models\PlatformSetting;
use App\Services\AdminFeeService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class AdminFeeSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static null|UnitEnum|string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Potongan Admin';

    protected static ?string $title = 'Potongan Admin';

    protected static ?string $slug = 'potongan-admin';

    protected string $view = 'filament.pages.admin-fee-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = PlatformSetting::current();

        $this->form->fill([
            'admin_fee_percentage' => $setting->admin_fee_percentage,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Komisi Admin')
                    ->description('Potongan dihitung dari subtotal produk vendor. Ongkir tidak ikut dipotong.')
                    ->schema([
                        TextInput::make('admin_fee_percentage')
                            ->label('Persentase Potongan Admin')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Contoh: isi 10 untuk potongan admin 10% dari subtotal vendor.'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(fn () => $this->save()),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        PlatformSetting::current()->update([
            'admin_fee_percentage' => round((float) ($state['admin_fee_percentage'] ?? 0), 2),
        ]);

        $this->form->fill([
            'admin_fee_percentage' => PlatformSetting::current()->admin_fee_percentage,
        ]);

        Notification::make()
            ->title('Potongan admin berhasil disimpan.')
            ->success()
            ->send();
    }

    /**
     * @return array{
     *     gross_amount: float,
     *     admin_fee_percentage: float,
     *     admin_fee_amount: float,
     *     vendor_payout_amount: float
     * }
     */
    public function getPreviewBreakdown(): array
    {
        return app(AdminFeeService::class)->calculateBreakdown(100000);
    }
}
