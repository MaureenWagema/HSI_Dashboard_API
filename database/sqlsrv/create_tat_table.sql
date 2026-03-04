CREATE TABLE TaT (
    Claim_No NVARCHAR(50) NOT NULL,
    Policy_No NVARCHAR(50),
    Name NVARCHAR(255),
    Dept NVARCHAR(100),
    Date_Reported DATE,
    Offer_Date DATE,
    statusdescription NVARCHAR(255),
    Time_to_Make_Offer INT,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    PRIMARY KEY (Claim_No)
);

CREATE INDEX IX_Tat_Offer_Date ON TaT(Offer_Date);
CREATE INDEX IX_Tat_Dept ON TaT(Dept);
CREATE INDEX IX_Tat_Time_to_Make_Offer ON TaT(Time_to_Make_Offer);
