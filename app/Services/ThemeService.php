<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Session;

class ThemeService
{
    public const THEME_FINTECH = 'fintech';
    public const THEME_CYBER = 'cyber';
    public const THEME_GAMEFUL = 'gameful';
    public const THEME_WEALTH = 'wealth';

    // Legacy fallback aliases
    public const MODE_GENERAL = 'fintech';
    public const MODE_RPG = 'cyber';

    /**
     * Get list of all available themes.
     *
     * @return array<string, array{id: string, name: string, description: string, badge: string, colors: string[]}>
     */
    public function getAvailableThemes(): array
    {
        return [
            self::THEME_FINTECH => [
                'id' => self::THEME_FINTECH,
                'name' => 'FinTech Minimal',
                'description' => 'Clean, modern digital banking experience with subtle gamification.',
                'badge' => '🏦 Minimal Banking',
                'colors' => ['#0f172a', '#2563eb', '#10b981', '#f8fafc'],
            ],
            self::THEME_CYBER => [
                'id' => self::THEME_CYBER,
                'name' => 'Cyber Finance',
                'description' => 'Dark futuristic command center with high-tech glowing telemetry.',
                'badge' => '⚡ Cyber Command',
                'colors' => ['#090d16', '#06b6d4', '#22c55e', '#ef4444'],
            ],
            self::THEME_GAMEFUL => [
                'id' => self::THEME_GAMEFUL,
                'name' => 'Gameful Finance',
                'description' => 'Vibrant, highly engaging RPG progression system inspired by modern productivity apps.',
                'badge' => '🎮 Gameful RPG',
                'colors' => ['#1e1b4b', '#8b5cf6', '#3b82f6', '#f59e0b'],
            ],
            self::THEME_WEALTH => [
                'id' => self::THEME_WEALTH,
                'name' => 'Premium Wealth',
                'description' => 'Luxury wealth management aesthetic with elegant serif typography and gold accents.',
                'badge' => '👑 Premium Wealth',
                'colors' => ['#18181b', '#d97706', '#059669', '#fafaf9'],
            ],
        ];
    }

    /**
     * Get the active theme key ('fintech', 'cyber', 'gameful', 'wealth').
     */
    public function getActiveMode(?User $user = null): string
    {
        $mode = null;
        if ($user && ! empty($user->theme_mode)) {
            $mode = $user->theme_mode;
        }

        if (! $mode) {
            $mode = Session::get('theme_mode', self::THEME_FINTECH);
        }

        // Map legacy aliases
        if ($mode === 'general') return self::THEME_FINTECH;
        if ($mode === 'rpg') return self::THEME_CYBER;

        return in_array($mode, [self::THEME_FINTECH, self::THEME_CYBER, self::THEME_GAMEFUL, self::THEME_WEALTH], true)
            ? $mode
            : self::THEME_FINTECH;
    }

    /**
     * Set active theme mode.
     */
    public function setMode(string $mode, ?User $user = null): void
    {
        $validMode = in_array($mode, [self::THEME_FINTECH, self::THEME_CYBER, self::THEME_GAMEFUL, self::THEME_WEALTH], true)
            ? $mode
            : self::THEME_FINTECH;

        Session::put('theme_mode', $validMode);

        if ($user) {
            $user->update(['theme_mode' => $validMode]);
        }
    }

    /**
     * Get design tokens & CSS utility classes for active theme.
     *
     * @return array<string, string>
     */
    public function getThemeTokens(string $mode): array
    {
        return match ($mode) {
            self::THEME_CYBER => [
                'bg_body' => 'bg-[#060a12] text-slate-100 font-sans',
                'header_bg' => 'bg-[#090e1a]/90 border-cyan-500/20 backdrop-blur-md',
                'card_bg' => 'bg-slate-950/80 border border-cyan-500/20 shadow-[0_0_15px_rgba(6,182,212,0.1)]',
                'card_critical' => 'bg-red-950/90 border border-red-500/60 shadow-[0_0_25px_rgba(239,68,68,0.3)]',
                'primary_accent' => 'text-cyan-400',
                'primary_bg' => 'bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold',
                'secondary_accent' => 'text-emerald-400',
                'badge_style' => 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30',
                'progress_bar' => 'bg-gradient-to-r from-cyan-500 via-teal-400 to-emerald-400',
                'font_heading' => 'font-display font-black tracking-tight',
            ],
            self::THEME_GAMEFUL => [
                'bg_body' => 'bg-[#0f111a] text-slate-100 font-sans',
                'header_bg' => 'bg-[#161927]/90 border-purple-500/20 backdrop-blur-md',
                'card_bg' => 'bg-slate-900/90 border border-purple-500/30 shadow-[0_0_20px_rgba(139,92,246,0.15)]',
                'card_critical' => 'bg-rose-950/90 border border-rose-500/60 shadow-[0_0_25px_rgba(244,63,94,0.3)]',
                'primary_accent' => 'text-purple-400',
                'primary_bg' => 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black',
                'secondary_accent' => 'text-amber-400',
                'badge_style' => 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
                'progress_bar' => 'bg-gradient-to-r from-purple-600 via-indigo-500 to-amber-400',
                'font_heading' => 'font-display font-black tracking-wide',
            ],
            self::THEME_WEALTH => [
                'bg_body' => 'bg-[#0c0d10] text-amber-50/90 font-serif',
                'header_bg' => 'bg-[#12141a]/95 border-amber-500/20 backdrop-blur-md',
                'card_bg' => 'bg-[#14161e] border border-amber-500/20 shadow-lg',
                'card_critical' => 'bg-rose-950/80 border border-rose-500/40',
                'primary_accent' => 'text-amber-400',
                'primary_bg' => 'bg-gradient-to-r from-amber-600 via-amber-500 to-yellow-600 hover:from-amber-500 hover:to-yellow-500 text-slate-950 font-bold',
                'secondary_accent' => 'text-emerald-400',
                'badge_style' => 'bg-amber-500/10 text-amber-300 border border-amber-500/30',
                'progress_bar' => 'bg-gradient-to-r from-amber-600 via-yellow-500 to-emerald-500',
                'font_heading' => 'font-serif font-bold tracking-tight',
            ],
            default => [ // FinTech Minimal
                'bg_body' => 'bg-[#0b1329] text-slate-100 font-sans',
                'header_bg' => 'bg-[#0f172a]/90 border-slate-800 backdrop-blur-md',
                'card_bg' => 'bg-slate-900/80 border border-slate-800 shadow-md',
                'card_critical' => 'bg-red-950/80 border border-red-800',
                'primary_accent' => 'text-blue-400',
                'primary_bg' => 'bg-blue-600 hover:bg-blue-500 text-white font-semibold',
                'secondary_accent' => 'text-emerald-400',
                'badge_style' => 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
                'progress_bar' => 'bg-gradient-to-r from-blue-600 via-indigo-500 to-emerald-400',
                'font_heading' => 'font-display font-bold tracking-tight',
            ],
        };
    }

    /**
     * Get dictionary of labels, terminology, and icons for active theme.
     *
     * @return array<string, string>
     */
    public function getLabels(string $mode): array
    {
        return match ($mode) {
            self::THEME_CYBER => [
                'brand_name' => 'CYBER FINANCE',
                'brand_tagline' => 'TACTICAL COMMAND CENTER',
                'nav_dashboard' => '⚡ Command Hub',
                'nav_transactions' => '📊 Telemetry',
                'nav_budget' => '🛡️ Shield Grid',
                'nav_goals' => '🎯 Vault Targets',
                'nav_analytics' => '📈 Data Core',
                'nav_challenges' => '⚡ Missions',
                'nav_achievements' => '🏆 Protocols',
                'nav_journey' => '🚀 Rank Journey',
                'nav_accounts' => '💳 Vault Accounts',
                'nav_settings' => '⚙️ System Config',
                'xp_name' => 'Energy',
                'level_name' => 'Rank',
                'quest_name' => 'Mission',
                'achievement_name' => 'Protocol',
                'streak_name' => 'Active Run',
                'hp_title' => 'SYSTEM INTEGRITY (HP)',
                'hp_unit' => 'HP',
                'hp_critical_badge' => '🚨 CRITICAL OVERLOAD (< 20%)',
                'hp_secure_badge' => '🛡️ INTEGRITY NOMINAL',
                'ap_title' => 'Energy Reserves',
                'ap_unit' => 'EP',
                'streak_title' => 'Active Run',
                'streak_unit' => 'Cycles',
                'ocr_card_title' => 'OCR Telemetry Parser',
                'ocr_rule_title' => 'Strict Validation Protocol:',
                'ocr_rule_desc' => 'Manual typing disabled. Upload receipt artifact to extract total expense value.',
                'allocator_title' => 'Energy Reserves (EP) Allocator',
                'allocator_desc' => 'Alokasikan sisa energi surplus budget ke target vault tabungan.',
                'evaluate_surplus_btn' => '⚡ Klaim Surplus Energy',
                'icon_xp' => '⚡',
                'icon_ap' => '⚡',
                'icon_level' => '🎖️',
                'icon_streak' => '🔥',
                'icon_hp' => '⚔️',
                'icon_ocr' => '📷',
                'theme_badge' => '⚡ Cyber Finance',
            ],

            self::THEME_GAMEFUL => [
                'brand_name' => 'GAMEFUL FINANCE',
                'brand_tagline' => 'FINANCIAL RPG QUEST SYSTEM',
                'nav_dashboard' => '🎮 Quest Hub',
                'nav_transactions' => '📜 Transaction Logs',
                'nav_budget' => '🛡️ Mana Budget',
                'nav_goals' => '🏆 Treasure Goals',
                'nav_analytics' => '🔮 Oracle Analytics',
                'nav_challenges' => '⚔️ Daily Quests',
                'nav_achievements' => '🏅 Trophy Room',
                'nav_journey' => '🗺️ Hero\'s Journey',
                'nav_accounts' => '👛 Item Pouch & Wallets',
                'nav_settings' => '⚙️ Options',
                'xp_name' => 'XP',
                'level_name' => 'Level',
                'quest_name' => 'Quest',
                'achievement_name' => 'Achievement',
                'streak_name' => 'Streak',
                'hp_title' => 'HEALTH POINTS (HP)',
                'hp_unit' => 'HP',
                'hp_critical_badge' => '🚨 LOW HP WARNING (< 20%)',
                'hp_secure_badge' => '🛡️ FULL HEALTH',
                'ap_title' => 'Action Points (AP)',
                'ap_unit' => 'AP',
                'streak_title' => 'Daily Streak',
                'streak_unit' => 'Days',
                'ocr_card_title' => 'Receipt Quest Scanner',
                'ocr_rule_title' => 'Anti-Cheat Quest Rule:',
                'ocr_rule_desc' => 'Scan retail receipt proof to validate expenses and claim bonus XP.',
                'allocator_title' => 'Action Points (AP) Treasure Allocator',
                'allocator_desc' => 'Alokasikan Action Points dari surplus budget ke harta karun target tabungan.',
                'evaluate_surplus_btn' => '🎮 Evaluasi Surplus Budget',
                'icon_xp' => '✨',
                'icon_ap' => '⚡',
                'icon_level' => '🎮',
                'icon_streak' => '🔥',
                'icon_hp' => '❤️',
                'icon_ocr' => '📜',
                'theme_badge' => '🎮 Gameful Finance',
            ],

            self::THEME_WEALTH => [
                'brand_name' => 'PREMIUM WEALTH',
                'brand_tagline' => 'LUXURY ASSET MANAGEMENT',
                'nav_dashboard' => '👑 Overview',
                'nav_transactions' => '💼 Ledger Entries',
                'nav_budget' => '🏛️ Fiscal Limits',
                'nav_goals' => '🥇 Wealth Targets',
                'nav_analytics' => '📈 Capital Insights',
                'nav_challenges' => '🏆 Milestones',
                'nav_achievements' => '🎖️ Distinction Gallery',
                'nav_journey' => '📜 Legacy Roadmap',
                'nav_accounts' => '🏛️ Liquidity Accounts',
                'nav_settings' => '⚙️ Preferences',
                'xp_name' => 'Wealth Points',
                'level_name' => 'Wealth Tier',
                'quest_name' => 'Financial Mission',
                'achievement_name' => 'Milestone',
                'streak_name' => 'Fiscal Discipline',
                'hp_title' => 'CAPITAL HEALTH SCORE',
                'hp_unit' => 'Score',
                'hp_critical_badge' => '⚠️ RESTRUCTURING REQUIRED',
                'hp_secure_badge' => '👑 CAPITAL OPTIMAL',
                'ap_title' => 'Surplus Credits',
                'ap_unit' => 'PTS',
                'streak_title' => 'Disciplined Streak',
                'streak_unit' => 'Days',
                'ocr_card_title' => 'Digital Audit Scanner',
                'ocr_rule_title' => 'Automated Audit Verification:',
                'ocr_rule_desc' => 'Scan transaction receipt to automatically audit and log expense allocations.',
                'allocator_title' => 'Surplus Capital Credit Allocator',
                'allocator_desc' => 'Alokasikan kredit surplus fiskal langsung ke portofolio target aset Anda.',
                'evaluate_surplus_btn' => '👑 Klaim Surplus Fiskal',
                'icon_xp' => '💎',
                'icon_ap' => '👑',
                'icon_level' => '👑',
                'icon_streak' => '🏛️',
                'icon_hp' => '⚜️',
                'icon_ocr' => '🧾',
                'theme_badge' => '👑 Premium Wealth',
            ],

            default => [ // FinTech Minimal
                'brand_name' => 'FINTECH MINIMAL',
                'brand_tagline' => 'SMART PERSONAL FINANCE',
                'nav_dashboard' => '🏠 Dashboard',
                'nav_transactions' => '💳 Transactions',
                'nav_budget' => '📊 Budget Planner',
                'nav_goals' => '🎯 Savings Goals',
                'nav_analytics' => '📈 Analytics',
                'nav_challenges' => '⚡ Challenges',
                'nav_achievements' => '🏆 Achievements',
                'nav_journey' => '🧭 Financial Journey',
                'nav_accounts' => '👛 Accounts & Wallets',
                'nav_settings' => '⚙️ Settings',
                'xp_name' => 'XP',
                'level_name' => 'Level',
                'quest_name' => 'Challenge',
                'achievement_name' => 'Achievement',
                'streak_name' => 'Streak',
                'hp_title' => 'FINANCIAL HEALTH SCORE',
                'hp_unit' => '/100',
                'hp_critical_badge' => '⚠️ HEALTH WARNING (< 20%)',
                'hp_secure_badge' => '✅ HEALTH OPTIMAL',
                'ap_title' => 'Action Points',
                'ap_unit' => 'AP',
                'streak_title' => 'Logging Streak',
                'streak_unit' => 'Days',
                'ocr_card_title' => 'Receipt Scanner (OCR)',
                'ocr_rule_title' => 'Automated Expense Verification:',
                'ocr_rule_desc' => 'Upload grocery or retail receipt image to extract transaction total instantly.',
                'allocator_title' => 'Action Points (AP) Allocator',
                'allocator_desc' => 'Alokasikan Action Points dari penghematan budget bulanan ke target tabungan riil Anda.',
                'evaluate_surplus_btn' => '⚡ Evaluasi Surplus Budget',
                'icon_xp' => '⚡',
                'icon_ap' => '⚡',
                'icon_level' => '⭐',
                'icon_streak' => '🔥',
                'icon_hp' => '💚',
                'icon_ocr' => '📷',
                'theme_badge' => '🏦 FinTech Minimal',
            ],
        };
    }
}
