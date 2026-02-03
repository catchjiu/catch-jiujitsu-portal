import React, { useState } from 'react';
import { ClassSession } from '../types';
import { GlassCard } from '../components/ui/GlassCard';

interface ScheduleProps {
  classes: ClassSession[];
  onBook: (classId: number) => void;
  onCancel: (classId: number) => void;
}

export const Schedule: React.FC<ScheduleProps> = ({ classes, onBook, onCancel }) => {
  const [filter, setFilter] = useState<'All' | 'Gi' | 'No-Gi'>('All');

  const filteredClasses = classes.filter(c => {
    if (filter === 'All') return true;
    if (filter === 'Gi') return c.type === 'Gi' || c.type === 'Fundamentals';
    if (filter === 'No-Gi') return c.type === 'No-Gi';
    return true;
  });

  const getCapacityColor = (booked: number, capacity: number) => {
    const percentage = booked / capacity;
    if (percentage >= 1) return 'bg-red-500';
    if (percentage >= 0.8) return 'bg-brand-gold';
    return 'bg-emerald-500';
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-display font-bold text-white uppercase tracking-wide">Schedule</h2>
        <div className="flex bg-slate-800 rounded-lg p-1">
          {['All', 'Gi', 'No-Gi'].map((f) => (
            <button
              key={f}
              onClick={() => setFilter(f as any)}
              className={`px-3 py-1 text-xs font-bold rounded-md transition-all ${
                filter === f ? 'bg-brand-blue text-white shadow-md' : 'text-slate-400 hover:text-white'
              }`}
            >
              {f}
            </button>
          ))}
        </div>
      </div>

      <div className="space-y-4">
        {filteredClasses.map((session) => {
          const isFull = session.bookedCount >= session.capacity;
          const capacityPercent = (session.bookedCount / session.capacity) * 100;
          const sessionDate = new Date(session.startTime);

          return (
            <GlassCard key={session.id} className="group">
              <div className="flex justify-between items-start mb-2">
                <div className="flex flex-col">
                  <span className="text-brand-gold text-xs font-bold uppercase tracking-wider mb-0.5">
                    {sessionDate.toLocaleDateString([], { weekday: 'long' })}
                  </span>
                  <span className="text-3xl font-display font-bold text-white">
                    {sessionDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                  </span>
                  <span className="text-slate-400 text-xs">90 Minutes</span>
                </div>
                <div className="text-right">
                   <span className={`inline-block px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border ${
                     session.type.includes('No-Gi') 
                      ? 'bg-purple-500/10 text-purple-400 border-purple-500/20' 
                      : 'bg-blue-500/10 text-blue-400 border-blue-500/20'
                   }`}>
                     {session.type}
                   </span>
                </div>
              </div>

              <div className="mb-4">
                <h3 className="text-lg font-bold text-slate-100">{session.title}</h3>
                <p className="text-sm text-slate-400">Instr: {session.instructor}</p>
              </div>

              {/* Capacity Bar */}
              <div className="mb-4">
                <div className="flex justify-between text-xs mb-1">
                  <span className="text-slate-400">Mat Capacity</span>
                  <span className={`${isFull ? 'text-red-400' : 'text-slate-300'}`}>
                    {session.bookedCount} / {session.capacity}
                  </span>
                </div>
                <div className="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                  <div 
                    className={`h-full transition-all duration-500 ${getCapacityColor(session.bookedCount, session.capacity)}`} 
                    style={{ width: `${capacityPercent}%` }}
                  />
                </div>
              </div>

              {/* Action Button */}
              {session.isBookedByUser ? (
                 <button 
                  onClick={() => onCancel(session.id)}
                  className="w-full py-2.5 rounded border border-red-500/50 text-red-400 font-bold text-sm hover:bg-red-500/10 transition-colors uppercase tracking-wide"
                 >
                   Cancel Booking
                 </button>
              ) : (
                <button 
                  disabled={isFull}
                  onClick={() => onBook(session.id)}
                  className={`w-full py-2.5 rounded font-bold text-sm uppercase tracking-wide transition-all shadow-lg ${
                    isFull 
                      ? 'bg-slate-700 text-slate-500 cursor-not-allowed' 
                      : 'bg-brand-blue hover:bg-brand-darkBlue text-white shadow-brand-blue/20'
                  }`}
                >
                  {isFull ? 'Class Full' : 'Book Class'}
                </button>
              )}
            </GlassCard>
          );
        })}
      </div>
    </div>
  );
};