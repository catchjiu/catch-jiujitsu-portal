import React, { useEffect, useRef } from 'react';
import { CheckInMember } from '../../types';

const VIEWPORT = { width: 1920, height: 1080 };
const DISPLAY_SECONDS = 4;

function getBeltColor(rank: string): string {
  switch (rank) {
    case 'White':
      return 'bg-gray-100 border-gray-300';
    case 'Blue':
      return 'bg-blue-600 border-blue-400';
    case 'Purple':
      return 'bg-purple-600 border-purple-400';
    case 'Brown':
      return 'bg-yellow-900 border-yellow-700';
    case 'Black':
      return 'bg-slate-900 border-red-600 border-l-8';
    default:
      return 'bg-gray-100';
  }
}

function playSound(active: boolean): void {
  try {
    const ctx = new (window.AudioContext || (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext)();
    const play = (freq: number, duration: number, type: OscillatorType = 'sine') => {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.frequency.value = freq;
      osc.type = type;
      gain.gain.setValueAtTime(0.15, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + duration);
    };
    if (active) {
      play(523.25, 0.15);
      setTimeout(() => play(659.25, 0.2), 120);
      setTimeout(() => play(783.99, 0.25), 280);
    } else {
      play(200, 0.4, 'square');
      setTimeout(() => play(180, 0.5, 'square'), 350);
    }
  } catch {
    // Ignore if AudioContext not supported
  }
}

interface MemberWelcomeScreenProps {
  member: CheckInMember;
  onDone: () => void;
}

export const MemberWelcomeScreen: React.FC<MemberWelcomeScreenProps> = ({ member, onDone }) => {
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    playSound(member.isActive);
  }, [member.isActive]);

  useEffect(() => {
    timerRef.current = setTimeout(onDone, DISPLAY_SECONDS * 1000);
    return () => {
      if (timerRef.current) clearTimeout(timerRef.current);
    };
  }, [onDone]);

  const expiryText = member.membershipExpiresAt
    ? new Date(member.membershipExpiresAt).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      })
    : '—';

  return (
    <div
      className="bg-slate-950 text-slate-100 font-sans flex items-center justify-center overflow-hidden"
      style={{
        width: VIEWPORT.width,
        height: VIEWPORT.height,
        boxSizing: 'border-box',
      }}
    >
      <div className="flex flex-col items-center max-w-4xl">
        {/* Status badge */}
        <div className="mb-8">
          {member.isActive ? (
            <span className="px-8 py-3 rounded-full text-xl font-display font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500/50">
              Active member
            </span>
          ) : (
            <span className="px-8 py-3 rounded-full text-xl font-display font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 border-2 border-amber-500/50">
              Membership expired
            </span>
          )}
        </div>

        {/* Avatar */}
        <div className="w-48 h-48 rounded-full overflow-hidden border-4 border-slate-600 bg-slate-800 shadow-2xl mb-8">
          {member.avatarUrl ? (
            <img
              src={member.avatarUrl}
              alt={member.name}
              className="w-full h-full object-cover"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-6xl font-display font-bold text-slate-500">
              {member.name
                .split(/\s+/)
                .map((s) => s[0])
                .join('')
                .slice(0, 2)
                .toUpperCase()}
            </div>
          )}
        </div>

        <h2 className="text-4xl font-display font-bold text-white uppercase tracking-wide mb-2">
          Welcome back
        </h2>
        <p className="text-3xl font-display font-bold text-brand-gold mb-10">{member.name}</p>

        {/* Belt graphic */}
        <div
          className={`h-14 w-full max-w-md rounded shadow-inner relative flex items-center justify-end pr-4 mb-10 ${getBeltColor(member.rank)}`}
        >
          <div className="h-full w-20 bg-black flex items-center justify-around px-1 absolute right-4">
            {[...Array(4)].map((_, i) => (
              <div
                key={i}
                className={`w-2 h-full rounded-sm ${i < member.stripes ? 'bg-white shadow-[0_0_8px_rgba(255,255,255,0.8)]' : 'bg-slate-700/50'}`}
              />
            ))}
          </div>
        </div>
        <p className="text-slate-400 text-xl mb-12">
          {member.rank} Belt {member.stripes > 0 ? ` · ${member.stripes} stripe${member.stripes > 1 ? 's' : ''}` : ''}
        </p>

        {/* Stats */}
        <div className="grid grid-cols-3 gap-8 w-full max-w-2xl">
          <div className="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
            <p className="text-4xl font-display font-bold text-brand-gold">{member.hoursThisYear}</p>
            <p className="text-slate-400 text-sm uppercase tracking-wider mt-1">Hours this year</p>
          </div>
          <div className="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
            <p className="text-4xl font-display font-bold text-brand-blue">{member.classesThisMonth}</p>
            <p className="text-slate-400 text-sm uppercase tracking-wider mt-1">Classes this month</p>
          </div>
          <div className="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
            <p className="text-2xl font-display font-bold text-white">{expiryText}</p>
            <p className="text-slate-400 text-sm uppercase tracking-wider mt-1">Expires</p>
          </div>
        </div>
      </div>
    </div>
  );
};
