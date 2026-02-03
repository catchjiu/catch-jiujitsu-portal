import React from 'react';

interface GlassCardProps {
  children: React.ReactNode;
  className?: string;
  onClick?: () => void;
}

export const GlassCard: React.FC<GlassCardProps> = ({ children, className = '', onClick }) => {
  return (
    <div 
      onClick={onClick}
      className={`
        relative overflow-hidden
        bg-slate-800/40 
        backdrop-blur-md 
        border border-white/10 
        rounded-2xl 
        p-5 
        shadow-lg
        transition-all duration-300
        ${onClick ? 'active:scale-[0.98] cursor-pointer hover:bg-slate-800/60' : ''}
        ${className}
      `}
    >
      {/* Subtle sheen effect */}
      <div className="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none" />
      <div className="relative z-10">
        {children}
      </div>
    </div>
  );
};