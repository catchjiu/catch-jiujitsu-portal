import React from 'react';
import { User, ClassSession } from '../types';
import { GlassCard } from '../components/ui/GlassCard';

interface DashboardProps {
  user: User;
  nextClass?: ClassSession;
}

export const Dashboard: React.FC<DashboardProps> = ({ user, nextClass }) => {
  
  // Helper for Belt Color
  const getBeltColor = (rank: string) => {
    switch(rank) {
      case 'White': return 'bg-gray-100 border-gray-300';
      case 'Blue': return 'bg-blue-600 border-blue-400';
      case 'Purple': return 'bg-purple-600 border-purple-400';
      case 'Brown': return 'bg-yellow-900 border-yellow-700';
      case 'Black': return 'bg-slate-900 border-red-600 border-l-8'; // Red bar for black belt
      default: return 'bg-gray-100';
    }
  };

  return (
    <div className="space-y-6">
      <div className="space-y-1">
        <h2 className="text-2xl font-display font-bold text-white uppercase tracking-wide">
          Welcome Back, <span className="text-brand-blue">{user.name.split(' ')[0]}</span>
        </h2>
        <p className="text-slate-400 text-sm">Ready to hit the mats?</p>
      </div>

      {/* Rank Card */}
      <GlassCard className="border-t-4 border-t-brand-gold">
        <div className="flex justify-between items-end">
          <div>
            <p className="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">Current Rank</p>
            <h3 className="text-3xl font-display font-bold text-white uppercase">{user.rank} Belt</h3>
          </div>
          <div className="text-right">
             <div className="flex space-x-1">
                {[...Array(4)].map((_, i) => (
                  <div key={i} className={`w-2 h-6 rounded-sm ${i < user.stripes ? 'bg-white shadow-[0_0_8px_rgba(255,255,255,0.8)]' : 'bg-slate-700/50'}`} />
                ))}
             </div>
          </div>
        </div>
        
        {/* Visual Belt Representation */}
        <div className={`mt-4 h-8 w-full rounded shadow-inner relative flex items-center justify-end pr-4 ${getBeltColor(user.rank)}`}>
           {/* Black Bar for colored belts */}
           {user.rank !== 'Black' && user.rank !== 'White' && (
             <div className="h-full w-16 bg-black flex items-center justify-around px-1 absolute right-4">
                {[...Array(user.stripes)].map((_, i) => (
                  <div key={i} className="w-1.5 h-full bg-white shadow-sm" />
                ))}
             </div>
           )}
           {/* Black Bar for White Belt */}
           {user.rank === 'White' && (
             <div className="h-full w-16 bg-black flex items-center justify-around px-1 absolute right-4">
                {[...Array(user.stripes)].map((_, i) => (
                  <div key={i} className="w-1.5 h-full bg-white shadow-sm" />
                ))}
             </div>
           )}
        </div>
      </GlassCard>

      {/* Stats Grid */}
      <div className="grid grid-cols-2 gap-4">
        <GlassCard>
          <div className="flex flex-col items-center justify-center py-2">
            <span className="text-4xl font-display font-bold text-brand-gold">{user.matHours}</span>
            <span className="text-xs text-slate-400 uppercase tracking-wider mt-1">Mat Hours</span>
          </div>
        </GlassCard>
        <GlassCard>
          <div className="flex flex-col items-center justify-center py-2">
            <span className="text-4xl font-display font-bold text-brand-blue">12</span>
            <span className="text-xs text-slate-400 uppercase tracking-wider mt-1">Classes / Mo</span>
          </div>
        </GlassCard>
      </div>

      {/* Next Class */}
      <div>
        <h3 className="text-lg font-display font-bold text-white mb-3 flex items-center gap-2">
          <span className="material-symbols-outlined text-brand-gold">event</span>
          MY NEXT CLASS
        </h3>
        {nextClass ? (
          <GlassCard className="bg-gradient-to-br from-slate-800 to-slate-900">
             <div className="flex justify-between items-start mb-2">
                <span className="px-2 py-1 rounded bg-brand-blue/20 text-brand-blue text-xs font-bold uppercase tracking-wider">
                  {nextClass.type}
                </span>
                <span className="text-slate-400 text-xs font-mono">
                  {new Date(nextClass.startTime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </span>
             </div>
             <h4 className="text-xl font-bold text-white mb-1">{nextClass.title}</h4>
             <p className="text-slate-400 text-sm mb-4">Instructor: {nextClass.instructor}</p>
             <button className="w-full py-3 rounded bg-slate-700 hover:bg-slate-600 text-white font-bold tracking-wide uppercase text-sm transition-colors border border-white/5">
                Check In Code
             </button>
          </GlassCard>
        ) : (
          <div className="p-6 rounded-2xl border-2 border-dashed border-slate-700 text-center text-slate-500">
            No upcoming classes booked.
          </div>
        )}
      </div>
    </div>
  );
};