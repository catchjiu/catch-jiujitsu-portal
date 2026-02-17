export type BeltRank = 'White' | 'Blue' | 'Purple' | 'Brown' | 'Black';

export type ClassType = 'Gi' | 'No-Gi' | 'Open Mat' | 'Fundamentals';

export interface User {
  id: number;
  name: string;
  email: string;
  rank: BeltRank;
  stripes: number;
  matHours: number;
  isAdmin: boolean;
  avatarUrl?: string;
}

export interface ClassSession {
  id: number;
  title: string;
  type: ClassType;
  startTime: string; // ISO string
  durationMinutes: number;
  instructor: string;
  capacity: number;
  bookedCount: number;
  isBookedByUser: boolean;
}

export type PaymentStatus = 'Pending Verification' | 'Paid' | 'Overdue' | 'Rejected';

export interface Payment {
  id: number;
  amount: number;
  month: string; // e.g., "October 2023"
  status: PaymentStatus;
  proofImageUrl?: string;
  submittedAt?: string;
}

/** Member data shown on check-in welcome screen (from QR lookup) */
export interface CheckInMember {
  id: number;
  name: string;
  rank: BeltRank;
  stripes: number;
  avatarUrl?: string;
  /** Total mat hours this year */
  hoursThisYear: number;
  /** Classes attended this month */
  classesThisMonth: number;
  /** Membership expiry date (ISO) */
  membershipExpiresAt: string | null;
  /** Whether membership is currently active */
  isActive: boolean;
}