import  { useState } from 'react';

function ProfilePage() {
    const [rollNo, setRollNo] = useState('12345');
    const [name, setName] = useState('John Doe');
    const [courses, setCourses] = useState('Course 1, Course 2');
    const [profilePicture, setProfilePicture] = useState(null);
    const [isEditing, setIsEditing] = useState(false);

    const handleProfilePictureChange = (e) => {
        setProfilePicture(URL.createObjectURL(e.target.files[0]));
    };

    const handleSave = () => {
        
        console.log('Profile saved');
        setIsEditing(false);
    };

    const handleEdit = () => {
        setIsEditing(true);
    };

    return (
        <div>
            <h1>Profile</h1>
            <div>
                <label>Roll No:</label>
                {isEditing ? (
                    <input
                        type="text"
                        value={rollNo}
                        onChange={(e) => setRollNo(e.target.value)}
                    />
                ) : (
                    <p>{rollNo}</p>
                )}
            </div>
            <div>
                <label>Name:</label>
                {isEditing ? (
                    <input
                        type="text"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                    />
                ) : (
                    <p>{name}</p>
                )}
            </div>
            <div>
                <label>Courses Enrolled:</label>
                {isEditing ? (
                    <input
                        type="text"
                        value={courses}
                        onChange={(e) => setCourses(e.target.value)}
                    />
                ) : (
                    <p>{courses}</p>
                )}
            </div>
            <div>
                <label>Profile Picture:</label>
                {isEditing ? (
                    <input
                        type="file"
                        onChange={handleProfilePictureChange}
                    />
                ) : (
                    profilePicture && <img src={profilePicture} alt="Profile" width="100" height="100" />
                )}
            </div>
            {isEditing ? (
                <button onClick={handleSave}>Save</button>
            ) : (
                <button onClick={handleEdit}>
                    <img src="https://img.icons8.com/ios-glyphs/30/000000/pencil.png" alt="Edit" />
                </button>
            )}
        </div>
    );
}

export default ProfilePage;