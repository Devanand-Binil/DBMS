import React, { useState } from 'react';

function MarkAttendancePage() {
    const [studentId, setStudentId] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        // Handle mark attendance logic here
        console.log(`Attendance marked for student ID: ${studentId}`);
    };

    return (
        <div>
            <h1>Mark Attendance</h1>
                <form onSubmit={handleSubmit}>
                <div>
                    <label>Student ID:</label>
                    <input
                        type="text"
                        value={studentId}
                        onChange={(e) => setStudentId(e.target.value)}
                        required
                    />
                </div>
                <button type="submit">Mark Attendance</button>
            </form>
        </div>
    );
}

export default MarkAttendancePage;