<?php

namespace App\Support;

use BladeUI\Icons\Factory as BladeIconsFactory;
use Filament\Support\Facades\FilamentIcon;

/**
 * Registrasi sistem ikon Tabler untuk seluruh aplikasi.
 *
 * - Mendaftarkan set blade-icons "tabler" (prefix tabler-*) dari SVG lokal,
 *   tanpa package tambahan.
 * - Meng-override alias ikon chrome Filament (sidebar, topbar, tabel, form,
 *   modal, notifikasi, pagination, dsb.) ke ikon Tabler.
 */
class TablerFilamentIcons
{
    public static function boot(): void
    {
        try {
            app(BladeIconsFactory::class)->add('tabler', [
                'prefix' => 'tabler',
                'path' => resource_path('svg/tabler'),
            ]);
        } catch (\Throwable) {
            // Set sudah terdaftar (mis. saat testing berulang) — abaikan.
        }

        FilamentIcon::register(static::aliases());
    }

    /**
     * @return array<string, string> alias Filament => nama ikon tabler-*
     */
    public static function aliases(): array
    {
        $t = static fn (string $name): string => "tabler-{$name}";

        return [
            // ---- tabel ----
            'tables::actions.filter' => $t('filter'),
            'tables::actions.group' => $t('dots'),
            'tables::actions.open-bulk-actions' => $t('chevron-down'),
            'tables::actions.column-manager' => $t('table'),
            'tables::columns.collapse-button' => $t('chevron-down'),
            'tables::columns.icon-column.true' => $t('check'),
            'tables::columns.icon-column.false' => $t('x'),
            'tables::empty-state' => $t('inbox'),
            'tables::filters.remove-all-button' => $t('x'),
            'tables::grouping.collapse-button' => $t('chevron-down'),
            'tables::header-cell.sort-asc-button' => $t('chevron-up'),
            'tables::header-cell.sort-button' => $t('chevrons-up-down'),
            'tables::header-cell.sort-desc-button' => $t('chevron-down'),
            'tables::reorder.handle' => $t('grip-vertical'),
            'tables::search-field' => $t('search'),
            // query builder
            'query-builder::add-rule-action' => $t('plus'),
            'query-builder::constraints.boolean' => $t('check'),
            'query-builder::constraints.date' => $t('calendar'),
            'query-builder::constraints.number' => $t('hash'),
            'query-builder::constraints.relationship' => $t('link'),
            'query-builder::constraints.select' => $t('list'),
            'query-builder::constraints.text' => $t('pencil'),
            'query-builder::or-group.block' => $t('layers'),
            'query-builder::or-group.add-group-action' => $t('plus'),
            // ---- actions ----
            'actions::action-group' => $t('dots'),
            'actions::create-action' => $t('plus'),
            'actions::create-action.grouped' => $t('plus'),
            'actions::delete-action' => $t('trash'),
            'actions::delete-action.grouped' => $t('trash'),
            'actions::delete-action.modal' => $t('trash'),
            'actions::detach-action' => $t('link-off'),
            'actions::detach-action.modal' => $t('link-off'),
            'actions::dissociate-action' => $t('link-off'),
            'actions::dissociate-action.modal' => $t('link-off'),
            'actions::edit-action' => $t('pencil'),
            'actions::edit-action.grouped' => $t('pencil'),
            'actions::export-action.grouped' => $t('download'),
            'actions::force-delete-action' => $t('trash'),
            'actions::force-delete-action.grouped' => $t('trash'),
            'actions::force-delete-action.modal' => $t('trash'),
            'actions::import-action.grouped' => $t('upload'),
            'actions::modal.confirmation' => $t('alert-triangle'),
            'actions::replicate-action' => $t('copy'),
            'actions::replicate-action.grouped' => $t('copy'),
            'actions::restore-action' => $t('history'),
            'actions::restore-action.grouped' => $t('history'),
            'actions::restore-action.modal' => $t('history'),
            'actions::view-action' => $t('eye'),
            'actions::view-action.grouped' => $t('eye'),
            // ---- panel chrome ----
            'panels::global-search.field' => $t('search'),
            'panels::pages.dashboard.actions.filter' => $t('filter'),
            'panels::pages.dashboard.navigation-item' => $t('layout-dashboard'),
            'panels::resources.pages.edit-record.navigation-item' => $t('pencil'),
            'panels::resources.pages.view-record.navigation-item' => $t('eye'),
            'panels::resources.pages.manage-related-records.navigation-item' => $t('settings'),
            'panels::sidebar.collapse-button' => $t('chevron-left'),
            'panels::sidebar.collapse-button.rtl' => $t('chevron-right'),
            'panels::sidebar.expand-button' => $t('chevron-right'),
            'panels::sidebar.expand-button.rtl' => $t('chevron-left'),
            'panels::sidebar.group.collapse-button' => $t('chevron-down'),
            'panels::sub-navigation.mobile-menu.button' => $t('menu'),
            'panels::theme-switcher.light-button' => $t('sun'),
            'panels::theme-switcher.dark-button' => $t('moon'),
            'panels::theme-switcher.system-button' => $t('device-desktop'),
            'panels::topbar.close-sidebar-button' => $t('x'),
            'panels::topbar.open-sidebar-button' => $t('menu'),
            'panels::topbar.group.toggle-button' => $t('dots'),
            'panels::topbar.open-database-notifications-button' => $t('bell'),
            'panels::sidebar.open-database-notifications-button' => $t('bell'),
            'panels::user-menu.profile-item' => $t('user'),
            'panels::user-menu.logout-button' => $t('logout'),
            'panels::user-menu.toggle-button' => $t('chevron-down'),
            'panels::widgets.account.logout-button' => $t('logout'),
            'panels::widgets.filament-info.open-documentation-button' => $t('book'),
            'panels::widgets.filament-info.open-github-button' => $t('code'),
            'panels::pages.password-reset.request-password-reset.actions.login' => $t('login'),
            'panels::pages.password-reset.request-password-reset.actions.login.rtl' => $t('login'),
            'panels::tenant-menu.billing-button' => $t('wallet'),
            'panels::tenant-menu.profile-button' => $t('user'),
            'panels::tenant-menu.registration-button' => $t('user-plus'),
            'panels::tenant-menu.toggle-button' => $t('chevron-down'),
            // ---- forms ----
            'forms::components.builder.actions.clone' => $t('copy'),
            'forms::components.builder.actions.collapse' => $t('chevron-down'),
            'forms::components.builder.actions.delete' => $t('trash'),
            'forms::components.builder.actions.expand' => $t('chevron-up'),
            'forms::components.builder.actions.move-down' => $t('arrow-down'),
            'forms::components.builder.actions.move-up' => $t('arrow-up'),
            'forms::components.builder.actions.reorder' => $t('grip-vertical'),
            'forms::components.checkbox-list.search-field' => $t('search'),
            'forms::components.file-upload.editor.actions.drag-crop' => $t('crop'),
            'forms::components.file-upload.editor.actions.drag-move' => $t('move'),
            'forms::components.file-upload.editor.actions.flip-horizontal' => $t('arrows-exchange'),
            'forms::components.file-upload.editor.actions.flip-vertical' => $t('arrows-exchange'),
            'forms::components.file-upload.editor.actions.move-down' => $t('arrow-down'),
            'forms::components.file-upload.editor.actions.move-left' => $t('arrow-left'),
            'forms::components.file-upload.editor.actions.move-right' => $t('arrow-right'),
            'forms::components.file-upload.editor.actions.move-up' => $t('arrow-up'),
            'forms::components.file-upload.editor.actions.rotate-left' => $t('rotate-counter-clockwise'),
            'forms::components.file-upload.editor.actions.rotate-right' => $t('rotate-clockwise'),
            'forms::components.file-upload.editor.actions.zoom-100' => $t('zoom'),
            'forms::components.file-upload.editor.actions.zoom-in' => $t('zoom-in'),
            'forms::components.file-upload.editor.actions.zoom-out' => $t('zoom-out'),
            'forms::components.key-value.actions.delete' => $t('trash'),
            'forms::components.key-value.actions.reorder' => $t('grip-vertical'),
            'forms::components.repeater.actions.clone' => $t('copy'),
            'forms::components.repeater.actions.collapse' => $t('chevron-down'),
            'forms::components.repeater.actions.delete' => $t('trash'),
            'forms::components.repeater.actions.expand' => $t('chevron-up'),
            'forms::components.repeater.actions.move-down' => $t('arrow-down'),
            'forms::components.repeater.actions.move-up' => $t('arrow-up'),
            'forms::components.repeater.actions.reorder' => $t('grip-vertical'),
            'forms::components.rich-editor.panels.custom-blocks.close-button' => $t('x'),
            'forms::components.rich-editor.panels.custom-block.delete-button' => $t('trash'),
            'forms::components.rich-editor.panels.custom-block.edit-button' => $t('pencil'),
            'forms::components.rich-editor.panels.merge-tags.close-button' => $t('x'),
            'forms::components.select.actions.create-option' => $t('plus'),
            'forms::components.select.actions.edit-option' => $t('pencil'),
            'forms::components.text-input.actions.copy' => $t('copy'),
            'forms::components.text-input.actions.hide-password' => $t('eye-off'),
            'forms::components.text-input.actions.show-password' => $t('eye'),
            'forms::components.toggle-buttons.boolean.false' => $t('x'),
            'forms::components.toggle-buttons.boolean.true' => $t('check'),
            // ---- schemas / infolists / widgets / support ----
            'schema::components.callout.danger' => $t('alert-triangle'),
            'schema::components.callout.info' => $t('info-circle'),
            'schema::components.callout.success' => $t('circle-check'),
            'schema::components.callout.warning' => $t('alert-triangle'),
            'schema::components.tabs.dropdown-trigger-button' => $t('chevron-down'),
            'schema::components.tabs.more-tabs-button' => $t('dots'),
            'schema::components.wizard.completed-step' => $t('check'),
            'infolists::components.icon-entry.false' => $t('x'),
            'infolists::components.icon-entry.true' => $t('check'),
            'widgets::chart-widget.filter' => $t('filter'),
            'badge.delete-button' => $t('x'),
            'breadcrumbs.separator' => $t('chevron-right'),
            'breadcrumbs.separator.rtl' => $t('chevron-left'),
            'modal.close-button' => $t('x'),
            'pagination.first-button' => $t('chevrons-left'),
            'pagination.first-button.rtl' => $t('chevrons-right'),
            'pagination.last-button' => $t('chevrons-right'),
            'pagination.last-button.rtl' => $t('chevrons-left'),
            'pagination.next-button' => $t('chevron-right'),
            'pagination.next-button.rtl' => $t('chevron-left'),
            'pagination.previous-button' => $t('chevron-left'),
            'pagination.previous-button.rtl' => $t('chevron-right'),
            'section.collapse-button' => $t('chevron-down'),
            // ---- notifications ----
            'notifications::database.modal.empty-state' => $t('bell'),
            'notifications::notification.close-button' => $t('x'),
            'notifications::notification.danger' => $t('alert-triangle'),
            'notifications::notification.info' => $t('info-circle'),
            'notifications::notification.success' => $t('circle-check'),
            'notifications::notification.warning' => $t('alert-triangle'),
        ];
    }
}
