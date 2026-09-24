import React, { useState, useEffect } from 'react';
import './CountdownTimer.css'; // Ensure this path is correct

const CountdownTimer = ({ endTime }) => {
    // Function to calculate the remaining time
    const calculateTimeRemaining = (endTime) => {
        const now = new Date();
        const timeDiff = endTime - now;
        const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
        return {
            hours,
            minutes,
            seconds,
            timeDiff,
        };
    };

    // Retrieve or set the end time
    const [storedEndTime, setStoredEndTime] = useState(() => {
        const storedTime = localStorage.getItem('endTime');
        return storedTime ? new Date(storedTime) : new Date(endTime);
    });

    const [timeRemaining, setTimeRemaining] = useState(() => calculateTimeRemaining(storedEndTime));

    useEffect(() => {
        // Update localStorage whenever the end time changes
        localStorage.setItem('endTime', storedEndTime.toISOString());

        const intervalId = setInterval(() => {
            const newTimeRemaining = calculateTimeRemaining(storedEndTime);
            setTimeRemaining(newTimeRemaining);
        }, 1000);

        // Clean up the interval on component unmount
        return () => clearInterval(intervalId);
    }, [storedEndTime]);

    // Calculate time remaining and check if the timer is running out
    const { hours, minutes, seconds, timeDiff } = timeRemaining;
    const isTimeRunningOut = timeDiff <= 10 * 60 * 1000; // Less than or equal to 10 minutes

    return (
        <div className={`timer ${isTimeRunningOut ? 'red' : ''}`}>
            {hours}:{minutes < 10 ? `0${minutes}` : minutes}:{seconds < 10 ? `0${seconds}` : seconds}
        </div>
    );
};

export default CountdownTimer;
