import React, { useEffect, useRef } from 'react';

const CameraFeed = () => {
    const videoRef = useRef(null);

    useEffect(() => {
        const getVideo = async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                if (videoRef.current) {
                    videoRef.current.srcObject = stream;
                }
            } catch (err) {
                console.error("Error accessing webcam: ", err);
            }
        };

        getVideo();
    }, []);

    return (
        <div style={{ width: '100px', height: '100px' }}>
            <video ref={videoRef} autoPlay style={{ width: '100%', height: '100%', objectFit: 'cover' }}></video>
        </div>
    );
};

export default CameraFeed;
