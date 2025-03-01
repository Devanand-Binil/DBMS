import React, { useState } from 'react';

function MarkAttendancePage() {
    const [studentId, setStudentId] = useState('');
    
    // Coordinates for National Institute of Technology Calicut (11.3204° N, 75.9374° E)
    const targetCoords = {
        latitude: 11.3204,
        
        longitude: 75.9374
    };

    const calculateDistance = (lat1, lon1, lat2, lon2) => {
        const R = 6371e3; // Earth radius in meters
        const lat1Rad = lat1 * Math.PI / 180;
        const lat2Rad = lat2 * Math.PI / 180;
        const deltaLat = (lat2 - lat1) * Math.PI / 180;
        const deltaLon = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
                  Math.cos(lat1Rad) * Math.cos(lat2Rad) *
                  Math.sin(deltaLon / 2) * Math.sin(deltaLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c; // Distance in meters
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!studentId) {
            alert('Please enter a Student ID');
            return;
        }

        try {
            // Get user's current position
            const userCoords = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(
                    (position) => resolve(position.coords),
                    (error) => reject(error)
                );
            });

            // Calculate distance between coordinates
            const distance = calculateDistance(
                userCoords.latitude,
                userCoords.longitude,
                targetCoords.latitude,
                targetCoords.longitude
            );

            if (distance <= 50) {
                // Simulate attendance marking
                console.log(`Attendance marked for ${studentId}`);
                alert('Attendance marked successfully!');
                setStudentId('');
            } else {
                alert(`You're ${Math.round(distance)}m away from NIT Calicut. Attendance not marked.`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert(`Error: ${error.message}`);
        }
    };

    return (
        <div>
            <h1>NIT Calicut Attendance System</h1>
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