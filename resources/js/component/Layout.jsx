import React from "react";
import Navbar from "./Navbar";

const Layout = ({children,peserta}) => {

    return (
        <div>
            <Navbar peserta={peserta}/>
            <div className="container mt-4">
                {children}
            </div>
        </div>
    )
}

export default Layout;
