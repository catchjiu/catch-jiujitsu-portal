import React from 'react';
import { User } from '../types';

interface LayoutProps {
  children: React.ReactNode;
  activeTab: string;
  onTabChange: (tab: string) => void;
  user: User;
  toggleAdmin: () => void;
}

export const Layout: React.FC<LayoutProps> = ({ children, activeTab, onTabChange, user, toggleAdmin }) => {
  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 pb-24 font-sans">
      {/* Top Bar */}
      <header className="fixed top-0 w-full z-50 bg-slate-950/80 backdrop-blur-md border-b border-white/5 px-6 py-4 flex justify-between items-center">
        <div className="flex items-center gap-2">
           {/* Logo placeholder */}
           <div className="w-8 h-8 rounded bg-gradient-to-br from-brand-gold to-orange-600 flex items-center justify-center font-display font-bold text-black text-lg">C</div>
           <h1 className="font-display font-bold text-xl tracking-wider text-white">CATCH <span className="text-brand-gold">BJJ</span></h1>
        </div>
        <div 
          onClick={toggleAdmin}
          className="w-8 h-8 rounded-full overflow-hidden border-2 border-slate-700 cursor-pointer"
        >
          <img src={user.avatarUrl} alt="User" className="w-full h-full object-cover" />
        </div>
      </header>

      {/* Main Content */}
      <main className="pt-20 px-4 max-w-lg mx-auto">
        {children}
      </main>

      {/* Bottom Navigation (Mobile First) */}
      <nav className="fixed bottom-0 left-0 w-full bg-slate-900/90 backdrop-blur-lg border-t border-white/10 z-50 pb-safe">
        <div className="flex justify-around items-center h-16 max-w-lg mx-auto">
          <NavButton 
            icon="dashboard" 
            label="Home" 
            isActive={activeTab === 'dashboard'} 
            onClick={() => onTabChange('dashboard')} 
          />
          <NavButton 
            icon="calendar_today" 
            label="Schedule" 
            isActive={activeTab === 'schedule'} 
            onClick={() => onTabChange('schedule')} 
          />
          <NavButton 
            icon="payments" 
            label="Payments" 
            isActive={activeTab === 'payments'} 
            onClick={() => onTabChange('payments')} 
          />
          {user.isAdmin && (
            <NavButton 
              icon="admin_panel_settings" 
              label="Admin" 
              isActive={activeTab === 'admin'} 
              onClick={() => onTabChange('admin')} 
            />
          )}
        </div>
      </nav>
    </div>
  );
};

const NavButton = ({ icon, label, isActive, onClick }: { icon: string, label: string, isActive: boolean, onClick: () => void }) => (
  <button 
    onClick={onClick}
    className={`flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors ${isActive ? 'text-brand-gold' : 'text-slate-500'}`}
  >
    <span className="material-symbols-outlined text-2xl">{icon}</span>
    <span className="text-[10px] font-medium tracking-wide">{label}</span>
  </button>
);