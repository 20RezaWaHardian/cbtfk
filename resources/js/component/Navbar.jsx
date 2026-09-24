import React, { useState, useEffect } from "react";
import 'bootstrap/dist/css/bootstrap.min.css';
import { Link } from "@inertiajs/inertia-react";

const Navbar = ({ peserta }) => {
    const [activeMenu, setActiveMenu] = useState('/');

    useEffect(() => {
        const currentPath = window.location.pathname;
        setActiveMenu(currentPath.replace('/', ''));
    }, []);

    return (
        <nav className="navbar navbar-expand-lg navbar-light" style={{ backgroundColor: 'skyblue' }}>
            <a className="navbar-brand fw-bold" style={{ marginLeft: '10px' }} href="#">CBT-FKIK</a>

            <div className="navbar-collapse justify-content-end" id="navbarNav">
                <ul className="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                    <li className="nav-item">
                        <a className="nav-link" href="#">
                            <div className="d-flex flex-column align-items-start">
                                <span className="fw-bold">{peserta.mahasiswa_rombel.nama_mahasiswa}</span>
                                <span className="text-muted">{peserta.mahasiswa_rombel.no_mhs}</span>
                            </div>
                        </a>
                    </li>

                </ul>
            </div>
        </nav>
    );
}

export default Navbar;
