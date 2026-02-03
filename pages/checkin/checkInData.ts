import { CheckInMember } from '../../types';

/** Mock members for QR lookup. In production, replace with API: GET /api/checkin?code=... */
const MOCK_MEMBERS: CheckInMember[] = [
  {
    id: 1,
    name: "Alex 'The Shark'",
    rank: 'Blue',
    stripes: 2,
    avatarUrl: 'https://picsum.photos/200',
    hoursThisYear: 42,
    classesThisMonth: 8,
    membershipExpiresAt: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
    isActive: true,
  },
  {
    id: 2,
    name: 'Jordan Lee',
    rank: 'Purple',
    stripes: 3,
    avatarUrl: 'https://picsum.photos/201',
    hoursThisYear: 68,
    classesThisMonth: 12,
    membershipExpiresAt: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
    isActive: false,
  },
  {
    id: 3,
    name: 'Sam Chen',
    rank: 'White',
    stripes: 4,
    hoursThisYear: 24,
    classesThisMonth: 6,
    membershipExpiresAt: new Date(Date.now() + 60 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
    isActive: true,
  },
];

/** Look up member by QR code. QR can contain member id (e.g. "1", "CATCH-1") or a token. */
export function lookupMemberByCode(code: string): CheckInMember | null {
  const trimmed = code.trim().toUpperCase().replace(/^CATCH-?/i, '');
  const id = parseInt(trimmed, 10);
  if (!Number.isNaN(id)) {
    const member = MOCK_MEMBERS.find((m) => m.id === id);
    return member ?? null;
  }
  return null;
}
