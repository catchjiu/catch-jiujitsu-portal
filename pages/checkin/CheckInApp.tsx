import React, { useState, useCallback } from 'react';
import { CheckInMember } from '../../types';
import { lookupMemberByCode } from './checkInData';
import { QRCheckInScreen } from './QRCheckInScreen';
import { MemberWelcomeScreen } from './MemberWelcomeScreen';

export const CheckInApp: React.FC = () => {
  const [member, setMember] = useState<CheckInMember | null>(null);

  const handleScan = useCallback((code: string) => {
    const found = lookupMemberByCode(code);
    if (found) {
      setMember(found);
    }
  }, []);

  const handleWelcomeDone = useCallback(() => {
    setMember(null);
  }, []);

  if (member) {
    return <MemberWelcomeScreen member={member} onDone={handleWelcomeDone} />;
  }

  return <QRCheckInScreen onScan={handleScan} />;
};
