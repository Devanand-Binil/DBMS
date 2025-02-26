import React, { useState } from 'react';

function ViewAttendancePage() {
    const [courseCode, setCourseCode] = useState('');
    const [attendance, setAttendance] = useState(null);

    const handleViewAttendance = () => {
      
        const fetchedAttendance = [
            { studentId: 'S001', status: 'Present' },
            { studentId: 'S002', status: 'Absent' },
           
        ];
        setAttendance(fetchedAttendance);
    };

    return (
        <div>
            <h1>View Attendance</h1>
            <div>
                <label>Course Code:</label>
                <input
                    type="text"
                    value={courseCode}
                    onChange={(e) => setCourseCode(e.target.value)}
                />
                <button onClick={handleViewAttendance}>View Attendance</button>
            </div>
            {attendance && (
                <div>
                    <h2>Attendance for {courseCode}</h2>
                    <ul>
                        {attendance.map((record, index) => (
                            <li key={index}>
                                Student ID: {record.studentId}, Status: {record.status}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}

export default ViewAttendancePage;