
import { useNavigate } from 'react-router-dom';

function StudentPage() {
    const navigate = useNavigate();

    const handleLogout = () => {
        // Handle logout logic here
        console.log('Logout successful');
        navigate('/');
    };

    const handleProfile = () => {
        // Navigate to profile page
        navigate('/profile');
    };

    const handleMarkAttendance = () => {
        // Navigate to mark attendance page
        navigate('/mark-attendance');
    };

    const handleViewAttendance = () => {
       
        navigate('/view-attendance');
    };

    return (
        <div>
            <button onClick={handleLogout}>Logout</button>
            <button onClick={handleProfile}>Profile</button>
            <button onClick={handleMarkAttendance}>Mark Attendance</button>
            <button onClick={handleViewAttendance}>View Attendance</button>
        </div>
    );
}

export default StudentPage;
