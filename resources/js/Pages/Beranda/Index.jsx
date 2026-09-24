import React, { useState, useEffect } from 'react';
import Layout from '../../component/Layout';
import axios from 'axios';
import sanitizeHtml from 'sanitize-html';
import '../../../css/soal.css';
import CameraFeed from '../../component/CameraFeed';
import CountdownTimer from '../../component/CountdownTimer';

export default function Index({ soal, id_ujian, id_peserta_ujian, answered_soal_ids,peserta,waktu_ujian}) {
    const [lembarJawaban, setLembarJawaban] = useState([]);
    const [selectedSoal, setSelectedSoal] = useState(null);
    const [selectedOption, setSelectedOption] = useState(null);
    const [dataAvailable, setDataAvailable] = useState(false);
    const [activeSoalId, setActiveSoalId] = useState(null);
    const [csrfToken, setCsrfToken] = useState('');
    const [endTime, setEndTime] = useState(null);

    useEffect(() => {
        const handleVisibilityChange = () => {
            if (document.hidden) {
                alert("Anda Terdeteksi Melakukan kecurangan dengan mencoba membuka tab baru atau aplikasi lainnya.");
            }
        };

        const handleBeforeUnload = (event) => {
            event.preventDefault();
            event.returnValue = '';
        };

        const handleKeyDown = (event) => {
            if (event.ctrlKey && (event.key === 't' || event.key === 'w')) {
                event.preventDefault();
                alert('Membuka atau menutup tab tidak diizinkan selama ujian.');
            }
        };

        document.addEventListener('visibilitychange', handleVisibilityChange);
        window.addEventListener('beforeunload', handleBeforeUnload);
        window.addEventListener('keydown', handleKeyDown);

        return () => {
            document.removeEventListener('visibilitychange', handleVisibilityChange);
            window.removeEventListener('beforeunload', handleBeforeUnload);
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, []);

    useEffect(() => {
        const answers = Array.from({ length: soal.length }, (_, i) => i + 1);
        setLembarJawaban(answers);
        setDataAvailable(soal.length > 0);
        fetchCsrfToken();

        // Fetch the first question
        if (soal.length > 0) {
            fetchSoal(soal[0].soal_id);
        }
        //  // Add beforeunload event listener
        //  const handleBeforeUnload = (event) => {
        //     event.preventDefault();
        //     event.returnValue = ''; // Most browsers ignore the custom message, but it needs to be set
        // };

        // window.addEventListener('beforeunload', handleBeforeUnload);

        // // Cleanup function
        // return () => {
        //     window.removeEventListener('beforeunload', handleBeforeUnload);
        // };


    }, [soal]);
    const fetchCsrfToken = async () => {
        try {
            const response = await axios.get('/csrf-token');
            setCsrfToken(response.data.csrfToken);
        } catch (error) {
            console.error('Error fetching CSRF token:', error);
        }
    };

    const fetchSoal = async (index) => {
        try {
            const response = await axios.get(`/soal/${index}/${id_peserta_ujian}/${id_ujian}`);
            const soalData = response.data;
            setSelectedSoal(soalData);
            setSelectedOption(soalData.jawaban_mhs || null); // Set jawaban yang sudah ada sebagai selectedOption jika ada
            setActiveSoalId(index);
        } catch (error) {
            console.error('Error fetching the question:', error);
        }
    };

    useEffect(() => {
        const startTime = new Date();
        if (waktu_ujian && waktu_ujian.durasi_ujian) {
            const durationInMinutes = parseInt(waktu_ujian.durasi_ujian, 10);
            if (isNaN(durationInMinutes)) {
                console.error('Invalid duration:', waktu_ujian.durasi_ujian);
                return;
            }
            const durationInMilliseconds = durationInMinutes * 60000;
            const calculatedEndTime = new Date(startTime.getTime() + durationInMilliseconds);
            setEndTime(calculatedEndTime);
        }
    }, [waktu_ujian]);

    if (!endTime) {
        return <div>Loading...</div>;
    }


    const sanitizeHtmlContent = (htmlContent) => {
        return sanitizeHtml(htmlContent, {
            allowedTags: [],
            allowedAttributes: {}
        });
    };

    const handleOptionChange = (event) => {
        setSelectedOption(event.target.value);
    };

    const handleNextButtonClick = () => {
        if (selectedOption) {
            const { idSoal } = selectedSoal;

            const dataToPost = {
                id_ujian: id_ujian,
                id_peserta_ujian: id_peserta_ujian,
                jawaban_mhs: selectedOption,
                id_soal: idSoal,
                _token: csrfToken
            };

            fetch('/jawaban', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(dataToPost),
            })
            .then(response => response.json())
            .then(data => {
                const nextSoalIndex = soal.findIndex(item => item.soal_id === activeSoalId) + 1;
                if (nextSoalIndex < soal.length) {
                    fetchSoal(soal[nextSoalIndex].soal_id);
                } else {
                    console.log('Sudah di soal terakhir');
                }
            })
            .catch(error => {
                console.error('Terjadi kesalahan saat posting data:', error);
            });
        } else {
            alert('Pilih opsi sebelum melanjutkan!');
        }
    };

    const handleFinishButtonClick = () => {
        // Tambahkan logika untuk mengakhiri ujian di sini
        if (selectedOption) {
            const { idSoal } = selectedSoal;

            const dataToPost = {
                id_ujian: id_ujian,
                id_peserta_ujian: id_peserta_ujian,
                jawaban_mhs: selectedOption,
                id_soal: idSoal,
                _token: csrfToken
            };

            fetch('/selesai-ujian', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(dataToPost),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.resource) {
                    window.location.href = data.resource;
                } else {
                    console.error('Data response does not contain resource:', data);
                }
            })
            .catch(error => {
                console.error('Terjadi kesalahan saat posting data:', error);
            });
        } else {
            alert('Pilih opsi sebelum melanjutkan!');
        }
    };

    const handleBeforeButtonClick = (event) => {
        // Tambahkan logika untuk mengakhiri ujian di sini
        const beforeSoalIndex = soal.findIndex(item => item.soal_id === activeSoalId) - 1;
                if (beforeSoalIndex < soal.length) {
                    fetchSoal(soal[beforeSoalIndex].soal_id);
                } else {
                    console.log('Sudah di soal terakhir');
                }
    };




    return (
        <Layout peserta={peserta}>
            <div className="soal-container">
                <div className="row">
                    <div className="col-12 d-flex justify-content-between align-items-center mb-2" >
                        <div className="camera-feed">
                            <CameraFeed />
                        </div>
                        <div className="timer">
                            Sisa Waktu :
                            <CountdownTimer endTime={endTime} />
                        </div>
                    </div>
                    <div className="col-8">
                        <div className="card">
                            <div className="card-body">
                                <div id="id_soal">
                                    {selectedSoal ? (
                                        <>
                                            <input type="hidden" name="idSoal" value={selectedSoal.idSoal} id="idSoal" />
                                            {selectedSoal.jenis === 'essay' ? (
                                                <div>
                                                    <p>{sanitizeHtmlContent(selectedSoal.pertanyaan)}</p>
                                                </div>
                                            ) : selectedSoal.jenis === 'pilgan' ? (
                                                <div>
                                                    <p>{sanitizeHtmlContent(selectedSoal.pertanyaan)}</p>
                                                    <div className="buttonColumn">
                                                    {selectedSoal.pilgan.kode.map((kode, index) => {
                                                        const letter = String.fromCharCode(65 + index);
                                                        const label = `${letter}. ${selectedSoal.pilgan.label[index]}`;

                                                        return (
                                                            <RadioViewButton
                                                                key={kode}
                                                                value={kode}
                                                                selectedOption={selectedOption}
                                                                onChange={handleOptionChange}
                                                                label={label}
                                                                disabled={!dataAvailable}
                                                            />
                                                        );
                                                    })}
                                                    </div>
                                                </div>
                                            ) : (
                                                <p>Jenis soal tidak dikenali</p>
                                            )}
                                        </>
                                    ) : (
                                        <p>Tunggu Beberapa Saat ...</p>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="mt-4">
                                {activeSoalId !== soal[0].soal_id && (
                                    <button className="btn btn-sm btn-primary" onClick={handleBeforeButtonClick}>Sebelumnya</button>
                                )}

                                {activeSoalId === soal[soal.length - 1].soal_id ? (
                                    <button className="btn btn-sm btn-success float-right" onClick={handleFinishButtonClick}>Selesai</button>
                                ) : (
                                    <button className="btn btn-sm btn-primary float-right" onClick={handleNextButtonClick}>Selanjutnya</button>
                                )}

                        </div>
                    </div>
                    <div className="col-4">
                        <div className="card">
                            <div className="card-header">Daftar Soal</div>
                            <div className="card-body">
                                {soal.map((item, index) => (
                                    <button
                                        className={`btn btn-sm btn-${activeSoalId === item.soal_id ? 'warning' : (answered_soal_ids.includes(item.soal_id) ? 'success' : 'primary')} m-1`}
                                        key={index}
                                        onClick={() => fetchSoal(item.soal_id)}
                                    >
                                        {index + 1}
                                    </button>
                                ))}
                            </div>
                            <div className="card-footer">LPTIK - UIN STS Jambi</div>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}

const RadioViewButton = ({ value, selectedOption, onChange, label, disabled }) => {
    const isChecked = selectedOption === value;

    return (
        <div className={`radio-view-button ${isChecked && !disabled ? 'selected' : ''}`}>
            <input
                type="radio"
                id={value}
                name="jawaban"
                value={value}
                checked={isChecked}
                onChange={onChange}
                className="hidden-radio"
                disabled={disabled}
            />
            <label htmlFor={value} className="radio-label">
                {label}
            </label>
        </div>
    );
};
