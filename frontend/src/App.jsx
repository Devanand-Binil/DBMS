
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import LoginPage from './components/LoginPage';
import StudentPage from './components/StudentPage';
import AdminPage from './components/AdminPage';
import ProfilePage from './components/ProfilePage';
import MarkAttendancePage from './components/MarkAttendancePage';
import ViewAttendancePage from './components/ViewAttendancePage';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<LoginPage />} />
        <Route path="/student" element={<StudentPage />} />
        <Route path="/admin" element={<AdminPage />} />
        <Route path="/profile" element={<ProfilePage />} />
        <Route path="/mark-attendance" element={<MarkAttendancePage />} />
        <Route path="/view-attendance" element={<ViewAttendancePage />} />
      </Routes>
    </Router>
  );
}

export default App;
